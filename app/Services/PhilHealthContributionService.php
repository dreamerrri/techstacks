<?php

namespace App\Services;

class PhilHealthContributionService
{
    /**
     * PhilHealth Contribution Table (2025/2026)
     * Salary Floor: ₱10,000 (minimum income baseline)
     * Salary Ceiling: ₱100,000 (maximum income baseline)
     * Premium Rate: 5.0% total (2.5% employee / 2.5% employer)
     */
    public function calculate(float $basicSalary): array
    {
        // Salary floor: ₱10,000
        $salaryBasis = max($basicSalary, 10000);
        
        // Salary ceiling: ₱100,000
        $salaryBasis = min($salaryBasis, 100000);
        
        // Calculate employee share (2.5% of salary basis)
        $employeeShare = $salaryBasis * 0.025;
        
        // Calculate employer share (2.5% of salary basis)
        $employerShare = $salaryBasis * 0.025;
        
        return [
            'salary_basis' => $salaryBasis,
            'employee_rate' => 0.025,
            'employer_rate' => 0.025,
            'employer_share' => $employerShare,
            'employee_share' => $employeeShare,
        ];
    }
}
