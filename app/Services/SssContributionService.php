<?php

namespace App\Services;

class SssContributionService
{
    /**
     * SSS Contribution Table (Official 2026)
     * Rate: 15% Total (10% Employer / 5% Employee)
     * EC Fund: ₱10 for MSC <= ₱14,500 | ₱30 for MSC >= ₱15,000
     * MPF is integrated into MSC above ₱20,000 (no separate MPF component)
     * Key: Monthly Salary Credit (MSC)
     * Value: [
     *   'employer_share' => float,
     *   'employee_share' => float,
     *   'ec' => float,
     *   'total' => float
     * ]
     */
    private $contributionTable = [
        5000 => ['employer_share' => 500.00, 'employee_share' => 250.00, 'ec' => 10.00, 'total' => 760.00],
        5500 => ['employer_share' => 550.00, 'employee_share' => 275.00, 'ec' => 10.00, 'total' => 835.00],
        6000 => ['employer_share' => 600.00, 'employee_share' => 300.00, 'ec' => 10.00, 'total' => 910.00],
        6500 => ['employer_share' => 650.00, 'employee_share' => 325.00, 'ec' => 10.00, 'total' => 985.00],
        7000 => ['employer_share' => 700.00, 'employee_share' => 350.00, 'ec' => 10.00, 'total' => 1060.00],
        7500 => ['employer_share' => 750.00, 'employee_share' => 337.50, 'ec' => 10.00, 'total' => 1097.50],
        8000 => ['employer_share' => 800.00, 'employee_share' => 400.00, 'ec' => 10.00, 'total' => 1210.00],
        8500 => ['employer_share' => 850.00, 'employee_share' => 425.00, 'ec' => 10.00, 'total' => 1285.00],
        9000 => ['employer_share' => 900.00, 'employee_share' => 450.00, 'ec' => 10.00, 'total' => 1360.00],
        9500 => ['employer_share' => 950.00, 'employee_share' => 475.00, 'ec' => 10.00, 'total' => 1435.00],
        10000 => ['employer_share' => 1000.00, 'employee_share' => 500.00, 'ec' => 10.00, 'total' => 1510.00],
        10500 => ['employer_share' => 1050.00, 'employee_share' => 525.00, 'ec' => 10.00, 'total' => 1585.00],
        11000 => ['employer_share' => 1100.00, 'employee_share' => 550.00, 'ec' => 10.00, 'total' => 1660.00],
        11500 => ['employer_share' => 1150.00, 'employee_share' => 575.00, 'ec' => 10.00, 'total' => 1735.00],
        12000 => ['employer_share' => 1200.00, 'employee_share' => 600.00, 'ec' => 10.00, 'total' => 1810.00],
        12500 => ['employer_share' => 1250.00, 'employee_share' => 625.00, 'ec' => 10.00, 'total' => 1885.00],
        13000 => ['employer_share' => 1300.00, 'employee_share' => 650.00, 'ec' => 10.00, 'total' => 1960.00],
        13500 => ['employer_share' => 1350.00, 'employee_share' => 675.00, 'ec' => 10.00, 'total' => 2035.00],
        14000 => ['employer_share' => 1400.00, 'employee_share' => 700.00, 'ec' => 10.00, 'total' => 2110.00],
        14500 => ['employer_share' => 1450.00, 'employee_share' => 725.00, 'ec' => 10.00, 'total' => 2185.00],
        15000 => ['employer_share' => 1500.00, 'employee_share' => 750.00, 'ec' => 30.00, 'total' => 2280.00],
        15500 => ['employer_share' => 1550.00, 'employee_share' => 775.00, 'ec' => 30.00, 'total' => 2355.00],
        16000 => ['employer_share' => 1600.00, 'employee_share' => 800.00, 'ec' => 30.00, 'total' => 2430.00],
        16500 => ['employer_share' => 1650.00, 'employee_share' => 825.00, 'ec' => 30.00, 'total' => 2505.00],
        17000 => ['employer_share' => 1700.00, 'employee_share' => 850.00, 'ec' => 30.00, 'total' => 2580.00],
        17500 => ['employer_share' => 1750.00, 'employee_share' => 875.00, 'ec' => 30.00, 'total' => 2655.00],
        18000 => ['employer_share' => 1800.00, 'employee_share' => 900.00, 'ec' => 30.00, 'total' => 2730.00],
        18500 => ['employer_share' => 1850.00, 'employee_share' => 925.00, 'ec' => 30.00, 'total' => 2805.00],
        19000 => ['employer_share' => 1900.00, 'employee_share' => 950.00, 'ec' => 30.00, 'total' => 2880.00],
        19500 => ['employer_share' => 1950.00, 'employee_share' => 975.00, 'ec' => 30.00, 'total' => 2955.00],
        20000 => ['employer_share' => 2000.00, 'employee_share' => 1000.00, 'ec' => 30.00, 'total' => 3030.00],
        20500 => ['employer_share' => 2050.00, 'employee_share' => 1025.00, 'ec' => 30.00, 'total' => 3105.00],
        21000 => ['employer_share' => 2100.00, 'employee_share' => 1050.00, 'ec' => 30.00, 'total' => 3180.00],
        21500 => ['employer_share' => 2150.00, 'employee_share' => 1075.00, 'ec' => 30.00, 'total' => 3255.00],
        22000 => ['employer_share' => 2200.00, 'employee_share' => 1100.00, 'ec' => 30.00, 'total' => 3330.00],
        22500 => ['employer_share' => 2250.00, 'employee_share' => 1125.00, 'ec' => 30.00, 'total' => 3405.00],
        23000 => ['employer_share' => 2300.00, 'employee_share' => 1150.00, 'ec' => 30.00, 'total' => 3480.00],
        23500 => ['employer_share' => 2350.00, 'employee_share' => 1175.00, 'ec' => 30.00, 'total' => 3555.00],
        24000 => ['employer_share' => 2400.00, 'employee_share' => 1200.00, 'ec' => 30.00, 'total' => 3630.00],
        24500 => ['employer_share' => 2450.00, 'employee_share' => 1225.00, 'ec' => 30.00, 'total' => 3705.00],
        25000 => ['employer_share' => 2500.00, 'employee_share' => 1250.00, 'ec' => 30.00, 'total' => 3780.00],
        25500 => ['employer_share' => 2550.00, 'employee_share' => 1275.00, 'ec' => 30.00, 'total' => 3855.00],
        26000 => ['employer_share' => 2600.00, 'employee_share' => 1300.00, 'ec' => 30.00, 'total' => 3930.00],
        26500 => ['employer_share' => 2650.00, 'employee_share' => 1325.00, 'ec' => 30.00, 'total' => 4005.00],
        27000 => ['employer_share' => 2700.00, 'employee_share' => 1350.00, 'ec' => 30.00, 'total' => 4080.00],
        27500 => ['employer_share' => 2750.00, 'employee_share' => 1375.00, 'ec' => 30.00, 'total' => 4155.00],
        28000 => ['employer_share' => 2800.00, 'employee_share' => 1400.00, 'ec' => 30.00, 'total' => 4230.00],
        28500 => ['employer_share' => 2850.00, 'employee_share' => 1425.00, 'ec' => 30.00, 'total' => 4305.00],
        29000 => ['employer_share' => 2900.00, 'employee_share' => 1450.00, 'ec' => 30.00, 'total' => 4380.00],
        29500 => ['employer_share' => 2950.00, 'employee_share' => 1475.00, 'ec' => 30.00, 'total' => 4455.00],
        30000 => ['employer_share' => 3000.00, 'employee_share' => 1500.00, 'ec' => 30.00, 'total' => 4530.00],
        30500 => ['employer_share' => 3050.00, 'employee_share' => 1525.00, 'ec' => 30.00, 'total' => 4605.00],
        31000 => ['employer_share' => 3100.00, 'employee_share' => 1550.00, 'ec' => 30.00, 'total' => 4680.00],
        31500 => ['employer_share' => 3150.00, 'employee_share' => 1575.00, 'ec' => 30.00, 'total' => 4755.00],
        32000 => ['employer_share' => 3200.00, 'employee_share' => 1600.00, 'ec' => 30.00, 'total' => 4830.00],
        32500 => ['employer_share' => 3250.00, 'employee_share' => 1625.00, 'ec' => 30.00, 'total' => 4905.00],
        33000 => ['employer_share' => 3300.00, 'employee_share' => 1650.00, 'ec' => 30.00, 'total' => 4980.00],
        33500 => ['employer_share' => 3350.00, 'employee_share' => 1675.00, 'ec' => 30.00, 'total' => 5055.00],
        34000 => ['employer_share' => 3400.00, 'employee_share' => 1700.00, 'ec' => 30.00, 'total' => 5130.00],
        34500 => ['employer_share' => 3450.00, 'employee_share' => 1725.00, 'ec' => 30.00, 'total' => 5205.00],
        35000 => ['employer_share' => 3500.00, 'employee_share' => 1750.00, 'ec' => 30.00, 'total' => 5280.00],
    ];

    /**
     * Calculate SSS contribution based on basic salary using official bracket table
     *
     * @param float $basicSalary
     * @return array
     */
    public function calculate(float $basicSalary): array
    {
        $salaryCredit = $this->getSalaryCredit($basicSalary);
        $contributions = $this->contributionTable[$salaryCredit] ?? $this->contributionTable[5000];

        return [
            'salary_credit' => $salaryCredit,
            'employee_share' => $contributions['employee_share'],
            'total' => $contributions['employee_share'],
        ];
    }

    /**
     * Get the Monthly Salary Credit based on basic salary using official bracket ranges
     *
     * @param float $basicSalary
     * @return int
     */
    private function getSalaryCredit(float $basicSalary): int
    {
        // Below ₱5,250: MSC = ₱5,000
        if ($basicSalary < 5250) {
            return 5000;
        }

        // ₱34,750 and above: MSC = ₱35,000
        if ($basicSalary >= 34750) {
            return 35000;
        }

        // Calculate MSC based on official bracket ranges
        // Each bracket covers a range of 500
        if ($basicSalary < 5750) return 5500;
        if ($basicSalary < 6250) return 6000;
        if ($basicSalary < 6750) return 6500;
        if ($basicSalary < 7250) return 7000;
        if ($basicSalary < 7750) return 7500;
        if ($basicSalary < 8250) return 8000;
        if ($basicSalary < 8750) return 8500;
        if ($basicSalary < 9250) return 9000;
        if ($basicSalary < 9750) return 9500;
        if ($basicSalary < 10250) return 10000;
        if ($basicSalary < 10750) return 10500;
        if ($basicSalary < 11250) return 11000;
        if ($basicSalary < 11750) return 11500;
        if ($basicSalary < 12250) return 12000;
        if ($basicSalary < 12750) return 12500;
        if ($basicSalary < 13250) return 13000;
        if ($basicSalary < 13750) return 13500;
        if ($basicSalary < 14250) return 14000;
        if ($basicSalary < 14750) return 14500;
        if ($basicSalary < 15250) return 15000;
        if ($basicSalary < 15750) return 15500;
        if ($basicSalary < 16250) return 16000;
        if ($basicSalary < 16750) return 16500;
        if ($basicSalary < 17250) return 17000;
        if ($basicSalary < 17750) return 17500;
        if ($basicSalary < 18250) return 18000;
        if ($basicSalary < 18750) return 18500;
        if ($basicSalary < 19250) return 19000;
        if ($basicSalary < 19750) return 19500;
        if ($basicSalary < 20250) return 20000;
        if ($basicSalary < 20750) return 20500;
        if ($basicSalary < 21250) return 21000;
        if ($basicSalary < 21750) return 21500;
        if ($basicSalary < 22250) return 22000;
        if ($basicSalary < 22750) return 22500;
        if ($basicSalary < 23250) return 23000;
        if ($basicSalary < 23750) return 23500;
        if ($basicSalary < 24250) return 24000;
        if ($basicSalary < 24750) return 24500;
        if ($basicSalary < 25250) return 25000;
        if ($basicSalary < 25750) return 25500;
        if ($basicSalary < 26250) return 26000;
        if ($basicSalary < 26750) return 26500;
        if ($basicSalary < 27250) return 27000;
        if ($basicSalary < 27750) return 27500;
        if ($basicSalary < 28250) return 28000;
        if ($basicSalary < 28750) return 28500;
        if ($basicSalary < 29250) return 29000;
        if ($basicSalary < 29750) return 29500;
        if ($basicSalary < 30250) return 30000;
        if ($basicSalary < 30750) return 30500;
        if ($basicSalary < 31250) return 31000;
        if ($basicSalary < 31750) return 31500;
        if ($basicSalary < 32250) return 32000;
        if ($basicSalary < 32750) return 32500;
        if ($basicSalary < 33250) return 33000;
        if ($basicSalary < 33750) return 33500;
        if ($basicSalary < 34250) return 34000;
        if ($basicSalary < 34750) return 34500;

        return 35000;
    }

    /**
     * Get all contribution brackets for reference
     *
     * @return array
     */
    public function getContributionBrackets(): array
    {
        return $this->contributionTable;
    }
}
