<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithholdingTaxTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the new withholding tax calculation formula.
     * Formula: (Total Monthly Gross - Total Monthly Contributions - Current Cutoff Allowances) - 33,333 = taxablePay
     * taxablePay * 20% + 1875 = Withholding Tax
     * Note: Only current cutoff allowances are used, not total monthly allowances (to avoid doubling)
     */
    public function test_withholding_tax_calculation_formula()
    {
        // 1. Create a user and employee
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'basic_salary' => 40000,
            'salary_type' => 'Monthly',
            'employment_status' => 'Regular',
            'date_hired' => now(),
            'is_archived' => false,
        ]);

        // 2. Create payroll periods for 1st and 2nd cutoff of June 2026
        $period1 = PayrollPeriod::create([
            'cutoff_start' => '2026-06-01',
            'cutoff_end' => '2026-06-15',
            'payroll_date' => '2026-06-15',
            'status' => 'draft',
        ]);

        $period2 = PayrollPeriod::create([
            'cutoff_start' => '2026-06-16',
            'cutoff_end' => '2026-06-30',
            'payroll_date' => '2026-06-30',
            'status' => 'draft',
        ]);

        // 3. Create payroll input for 1st cutoff
        // Gross pay = 20,000
        $input1 = new PayrollInput([
            'payroll_period_id' => $period1->id,
            'employee_id' => $employee->id,
            'daily_rate' => 1846.15, // (40000 * 12) / 52 / 40 * 8
            'rate_type' => 'daily',
            'days_worked' => 10.83, // Approximately half of 21.67 working days
        ]);
        // Manually set gross_pay to ensure exact calculation in test
        $input1->gross_pay = 20000;
        $input1->net_pay = 20000 - (1750/2 + 1000/2 + 200/2); // Minus contributions, no tax in 1st cutoff
        $input1->save();

        // 4. Calculate expected tax for 2nd cutoff
        // 2nd Cutoff Gross = 20,000
        // Total Monthly Gross = 40,000
        // SSS Monthly (MSC 35,000) = 1,750
        // PH Monthly (40,000 * 2.5%) = 1,000
        // PI Monthly (Fixed) = 200
        // Total Monthly Contributions = 2,950
        // Current Cutoff Allowances = 0 (no allowances in this test)
        // Taxable Income = 40,000 - 2,950 - 0 = 37,050
        // taxablePay (excess over 33,333) = 37,050 - 33,333 = 3,717
        // Expected Tax = (3,717 * 0.20) + 1,875 = 743.40 + 1,875 = 2,618.40

        $input2 = new PayrollInput([
            'payroll_period_id' => $period2->id,
            'employee_id' => $employee->id,
            'daily_rate' => 1846.15,
            'rate_type' => 'daily',
            'days_worked' => 10.84,
        ]);
        
        // This will trigger computePay() which uses our new logic
        $input2->computePay();
        
        // For the test to work accurately with computePay, we need to ensure the engine 
        // produces 20,000 gross for the 2nd cutoff.
        // We'll manually override gross_pay for the second cutoff calculation in the model if needed, 
        // but here we just want to verify the tax calculation logic inside computePay.
        
        // Let's manually trigger the private calculateTax via Reflection for a pure logic test
        $reflection = new \ReflectionClass($input2);
        $method = $reflection->getMethod('calculateTax');
        $method->setAccessible(true);
        
        $totalMonthlyGross = 40000;
        $totalMonthlyContributions = 2950;
        $currentCutoffAllowances = 0;
        $calculatedTax = $method->invoke($input2, $totalMonthlyGross, $totalMonthlyContributions, $currentCutoffAllowances);
        
        $this->assertEquals(2618.40, $calculatedTax);
    }
}
