<?php

namespace App\Services;

class SssContributionService
{
    /**
     * SSS Contribution Rates (Official 2026)
     * Total rate: 15% of MSC (10% Employer / 5% Employee)
     * EC Fund: ₱10 for MSC < ₱15,000 | ₱30 for MSC >= ₱15,000
     * MSC range: ₱5,000 to ₱35,000, in ₱500 increments
     */
    private const MSC_MIN = 5000;
    private const MSC_MAX = 35000;
    private const MSC_STEP = 500;
    private const EMPLOYEE_RATE = 0.05;
    private const EMPLOYER_RATE = 0.10;
    private const EC_THRESHOLD = 15000;
    private const EC_LOW = 10.00;
    private const EC_HIGH = 30.00;

    /**
     * Calculate SSS contribution based on basic salary.
     *
     * @param float $basicSalary
     * @return array
     */
    public function calculate(float $basicSalary): array
    {
        $msc = $this->getSalaryCredit($basicSalary);
        $contribution = $this->computeContribution($msc);

        return [
            'salary_credit' => $msc,
            'employee_share' => $contribution['employee_share'],
            'total' => $contribution['employee_share'],
        ];
    }

    /**
     * Get the Monthly Salary Credit based on basic salary.
     * MSC bands are ₱500 increments; each band's lower boundary
     * is offset by ₱250 (e.g. ₱5,250–5,749.99 => MSC ₱5,500).
     *
     * @param float $basicSalary
     * @return int
     */
    private function getSalaryCredit(float $basicSalary): int
    {
        $msc = (int) (floor(($basicSalary - 250) / self::MSC_STEP) * self::MSC_STEP + self::MSC_STEP);

        return max(self::MSC_MIN, min(self::MSC_MAX, $msc));
    }

    /**
     * Compute employer/employee/EC/total contribution for a given MSC.
     *
     * @param int $msc
     * @return array
     */
    private function computeContribution(int $msc): array
    {
        $employerShare = round($msc * self::EMPLOYER_RATE, 2);
        $employeeShare = round($msc * self::EMPLOYEE_RATE, 2);
        $ec = $msc >= self::EC_THRESHOLD ? self::EC_HIGH : self::EC_LOW;

        return [
            'employer_share' => $employerShare,
            'employee_share' => $employeeShare,
            'ec' => $ec,
            'total' => $employerShare + $employeeShare + $ec,
        ];
    }

    /**
     * Get all contribution brackets for reference (regenerated from formula,
     * same shape as the old hardcoded table).
     *
     * @return array
     */
    public function getContributionBrackets(): array
    {
        $brackets = [];
        for ($msc = self::MSC_MIN; $msc <= self::MSC_MAX; $msc += self::MSC_STEP) {
            $brackets[$msc] = $this->computeContribution($msc);
        }

        return $brackets;
    }
}