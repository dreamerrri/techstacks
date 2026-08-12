<?php

namespace App\Services\Payroll;


class PayrollComputationEngine
{

    /**
     * Compute payroll with attendance, manual adjustments, and preview support.
     *
     * @param array $employeeData
     * @param array $attendance
     * @param array $deductions
     * @param array $benefits
     * @param array $allowances
     * @param array $manualAdjustments (optional, e.g. ['add' => 100, 'subtract' => 50])
     * @param bool $preview (optional, if true, returns breakdown without applying deductions)
     * @param string|null $cutoffStart (optional, cutoff start date for calculating working days)
     * @param string|null $cutoffEnd (optional, cutoff end date for calculating working days)
     * @return array
     */
    public function compute(
        array $employeeData,
        array $attendance,
        array $deductions,
        array $benefits = [],
        array $allowances = [],
        array $manualAdjustments = [],
        bool $preview = false,
        ?string $cutoffStart = null,
        ?string $cutoffEnd = null
    ) {
        // Daily rate and working hours
        $dailyRate = $employeeData['daily_rate'] ?? 0;
        $workingHoursPerDay = $employeeData['working_hours_per_day'] ?? 8;
        
        // Calculate hourly rate
        $hourlyRate = round($dailyRate / $workingHoursPerDay, 2);

        // 1. Basic Salary - calculate based on days worked (cutoff period)
        // Daily Rate × Days Worked = Cutoff Pay
        $daysWorked = $attendance['days_worked'] ?? 0;
        $basicSalary = round($dailyRate * $daysWorked, 2);

        // 2. Overtime Pay
        $overtimeHours = $attendance['overtime_hours'] ?? 0;
        $overtimePay = round($hourlyRate * $overtimeHours, 2);

        // 3. Holiday Pay (daily rate × holiday days)
        // Support both 'holiday_days' and 'regular_holiday_worked' for compatibility
        $holidayDays = $attendance['holiday_days'] ?? ($attendance['regular_holiday_worked'] ?? 0);
        $holidayPay = round($dailyRate * 2 * $holidayDays, 2);

        // 4. Weekend Pay (daily rate × 30% × weekend days worked)
        $weekendsWorked = $attendance['weekends_worked'] ?? 0;
        $weekendPay = round($dailyRate * 0.30 * $weekendsWorked, 2);

        // 4. Night Differential
        // Support both 'night_hours' and 'night_differential_hours' for compatibility
        $nightHours = $attendance['night_hours'] ?? ($attendance['night_differential_hours'] ?? 0);
        $nightDiff = round(($hourlyRate * 0.10) * $nightHours, 2);

        // 5. Late Deduction
        $lateHours = $attendance['late_hours'] ?? 0;
        $lateDeduction = round($hourlyRate * $lateHours, 2);

        // 6. Allowances & Benefits
        $totalAllowances = round(array_sum($allowances), 2);
        $totalBenefits = round(array_sum($benefits), 2);

        // 7. Manual Adjustments
        $manualAdd = isset($manualAdjustments['add']) ? (float)$manualAdjustments['add'] : 0.0;
        $manualSubtract = isset($manualAdjustments['subtract']) ? (float)$manualAdjustments['subtract'] : 0.0;

        // 8. Gross Pay
        // Allowances are included in gross pay but subtracted from taxable income for withholding tax
        $grossPay = $basicSalary + $weekendPay + $overtimePay + $holidayPay + $nightDiff + $totalBenefits + $totalAllowances + $manualAdd - $manualSubtract - $lateDeduction;
        $grossPay = round($grossPay, 2);

        // 9. Deductions
        $totalDeductions = round(array_sum($deductions), 2);

        // 10. Net Pay
        $netPay = $preview ? $grossPay : round($grossPay - $totalDeductions, 2);

        return [
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
            'basic_salary' => $basicSalary,
            'weekend_pay' => $weekendPay,
            'overtime_pay' => $overtimePay,
            'holiday_pay' => $holidayPay,
            'night_differential' => $nightDiff,
            'late_deduction' => $lateDeduction,
            'allowances' => $totalAllowances,
            'benefits' => $totalBenefits,
            'manual_add' => $manualAdd,
            'manual_subtract' => $manualSubtract,
            'deductions' => $totalDeductions,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'preview' => $preview,
        ];
    }

    /**
     * Calculate working days between two dates (excluding weekends: Saturday and Sunday)
     */
    private function calculateWorkingDays($startDate, $endDate): int
    {
        $start = $startDate instanceof \DateTime ? $startDate : new \DateTime($startDate);
        $end = $endDate instanceof \DateTime ? $endDate : new \DateTime($endDate);
        
        $workingDays = 0;
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end->modify('+1 day'));
        
        foreach ($period as $day) {
            // Exclude Saturday (6) and Sunday (0)
            if ($day->format('N') != 6 && $day->format('N') != 0) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }
}
