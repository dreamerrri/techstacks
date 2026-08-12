<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Payroll\PayrollComputationEngine;

class PayrollComputationEngineBreakdownTest extends TestCase
{
    public function testPayrollBreakdownMatchesSample()
    {
        $engine = new PayrollComputationEngine();
        $employeeData = [
            'daily_rate' => 1136.36, // 25000 / 22 working days
            'working_hours_per_day' => 8,
        ];
        $attendance = [
            'days_worked' => 11, // cutoff
            'overtime_hours' => 6,
            'holiday_days' => 1, // 1 regular holiday in cutoff
            'night_hours' => 4,
            'late_hours' => 0,
        ];
        $allowances = [1000, 500, 1500]; // rice, internet, incentives
        $deductions = [562.50, 250, 100, 450, 1000, 142.05]; // SSS, PhilHealth, Pag-IBIG, Tax, Cash Advance, Late

        $result = $engine->compute($employeeData, $attendance, $deductions, [], $allowances);

        $this->assertEquals(1136.36, $result['daily_rate']);
        $this->assertEquals(142.05, $result['hourly_rate']);
        $this->assertEquals(12499.96, $result['basic_salary']);
        $this->assertEquals(852.30, $result['overtime_pay']); // 142.05 * 6 hours
        $this->assertEquals(2272.72, $result['holiday_pay']);
        $this->assertEquals(56.82, $result['night_differential']);
        $this->assertEquals(0.00, $result['late_deduction']);
        $this->assertEquals(3000.00, $result['allowances']);
        $this->assertEquals(0.00, $result['benefits']);
        $this->assertEquals(18681.80, $result['gross_pay']); // Now includes allowances (3000)
        $this->assertEquals(2504.55, $result['deductions']);
        $this->assertEquals(16177.25, $result['net_pay']);
    }
}
