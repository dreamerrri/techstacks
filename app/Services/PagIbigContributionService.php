<?php

namespace App\Services;

class PagIbigContributionService
{
    /**
     * Pag-IBIG Contribution Table (2026)
     * Monthly Compensation Range | Employee Share | Employer Share | Total Monthly Savings
     * P1,500 and below           | 1% of salary   | 2% of salary   | Varies
     * P1,501 to P10,000          | 2% of salary   | 2% of salary   | Up to P400
     * P10,001 and above          | P200.00        | P200.00        | P400.00
     */
    public function calculate(float $basicSalary): array
    {
        if ($basicSalary <= 1500) {
            // P1,500 and below: Employee 1%, Employer 2%
            $employeeShare = $basicSalary * 0.01;
            $employerShare = $basicSalary * 0.02;
        } elseif ($basicSalary <= 10000) {
            // P1,501 to P10,000: Employee 2%, Employer 2%
            $employeeShare = $basicSalary * 0.02;
            $employerShare = $basicSalary * 0.02;
        } else {
            // P10,001 and above: Employee P200, Employer P200
            $employeeShare = 200.00;
            $employerShare = 200.00;
        }

        return [
            'salary' => $basicSalary,
            'employee_rate' => $basicSalary <= 1500 ? 0.01 : ($basicSalary <= 10000 ? 0.02 : null),
            'employer_rate' => $basicSalary <= 1500 ? 0.02 : ($basicSalary <= 10000 ? 0.02 : null),
            'employee_share' => $employeeShare,
            'employer_share' => $employerShare,
            'total' => $employeeShare + $employerShare,
        ];
    }
}
