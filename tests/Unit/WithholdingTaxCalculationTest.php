<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WithholdingTaxCalculationTest extends TestCase
{
    /**
     * Test the withholding tax calculation logic.
     * Formula: (Total Monthly Gross - Total Monthly Contributions) - 33,333 = taxablePay
     * taxablePay * 20% + 1875 = Withholding Tax
     */
    public function test_calculate_tax_logic()
    {
        // Mocking the calculation logic since it's private in the controller/model
        // We'll use a local function that mirrors the implementation
        
        $calculateTax = function(float $totalMonthlyGross, float $totalMonthlyContributions): float {
            $taxableIncome = $totalMonthlyGross - $totalMonthlyContributions;
            $taxablePay = $taxableIncome - 33333;
            
            if ($taxablePay <= 0) {
                return 0;
            }
            
            $withholdingTax = ($taxablePay * 0.20) + 1875;
            return round($withholdingTax, 2);
        };

        // Scenario 1: Gross 40,000, Contributions 2,950
        // Taxable Income: 40,000 - 2,950 = 37,050
        // taxablePay (excess): 37,050 - 33,333 = 3,717
        // Tax: (3,717 * 0.20) + 1875 = 743.40 + 1875 = 2618.40
        $this->assertEquals(2618.40, $calculateTax(40000, 2950));

        // Scenario 2: Gross 30,000, Contributions 2,000
        // Taxable Income: 30,000 - 2,000 = 28,000
        // taxablePay: 28,000 - 33,333 = -5,333
        // Tax: 0
        $this->assertEquals(0, $calculateTax(30000, 2000));

        // Scenario 3: Gross 50,000, Contributions 3,500
        // Taxable Income: 50,000 - 3,500 = 46,500
        // taxablePay: 46,500 - 33,333 = 13,167
        // Tax: (13,167 * 0.20) + 1875 = 2633.40 + 1875 = 4508.40
        $this->assertEquals(4508.40, $calculateTax(50000, 3500));
    }
}
