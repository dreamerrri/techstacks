<?php

namespace App\Services;

class WithholdingTaxService
{
    /**
     * Calculate monthly withholding tax per BIR's revised (TRAIN law) table.
     *
     * Formula per bracket: Tax = Base + (Rate x (Taxable Income - Floor))
     *
     * @param float $totalMonthlyGross Total gross pay for the month (both cutoffs combined)
     * @param float $totalMonthlyContributions Total SSS + PhilHealth + Pag-IBIG for the month
     * @param float $totalMonthlyAllowances Allowances for the current cutoff (treated as advance pay, not doubled)
     * @return array{tax: float, taxable_income: float, bracket: int}
     */
    public function calculate(float $totalMonthlyGross, float $totalMonthlyContributions, float $totalMonthlyAllowances = 0): array
    {
        $taxableIncome = $totalMonthlyGross - $totalMonthlyContributions - $totalMonthlyAllowances;

        $brackets = [
            // [floor, ceiling (null = no ceiling), base tax, rate]
            [0,        20833,  0,          0.00],
            [20833,    33332,  0,          0.15],
            [33333,    66666,  1875,       0.20],
            [66667,    166666, 13541.80,   0.25],
            [166667,   666666, 90841.80,   0.30],
            [666667,   null,   200841.80,  0.35],
        ];

        foreach ($brackets as $index => [$floor, $ceiling, $base, $rate]) {
            if ($ceiling === null || $taxableIncome <= $ceiling) {
                $taxablePay = $taxableIncome - $floor;
                $tax = $taxablePay > 0 ? round(($taxablePay * $rate) + $base, 2) : 0;

                return [
                    'tax' => $tax,
                    'taxable_income' => $taxableIncome,
                    'bracket' => $index + 1,
                ];
            }
        }

        // Unreachable: last bracket has no ceiling and always matches
        return ['tax' => 0, 'taxable_income' => $taxableIncome, 'bracket' => 0];
    }
}