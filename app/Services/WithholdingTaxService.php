<?php

namespace App\Services;

class WithholdingTaxService
{
    /**
     * Calculate monthly withholding tax per BIR's revised (TRAIN law) table,
     * effective January 1, 2023 onwards.
     */
    public function calculate(float $totalMonthlyGross, float $totalMonthlyContributions, float $totalMonthlyAllowances = 0): array
    {
        $taxableIncome = $totalMonthlyGross - $totalMonthlyContributions - $totalMonthlyAllowances;

        $brackets = [
            // [floor, ceiling (null = no ceiling), base tax, rate]
            [0,        20833,  0,          0.00],
            [20833,    33332,  0,          0.15],
            [33333,    66666,  1875,       0.20],
            [66667,    166666, 8541.80,    0.25],   // corrected from 13541.80
            [166667,   666666, 33541.80,   0.30],   // corrected from 90841.80
            [666667,   null,   183541.80,  0.35],   // corrected from 200841.80
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

        return ['tax' => 0, 'taxable_income' => $taxableIncome, 'bracket' => 0];
    }
}