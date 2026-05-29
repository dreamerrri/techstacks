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
     * @return array
     */
    public function compute(
        array $employeeData,
        array $attendance,
        array $deductions,
        array $benefits = [],
        array $allowances = [],
        array $manualAdjustments = [],
        bool $preview = false
    ) {
        // Monthly salary and divisor
        $monthlySalary = $employeeData['monthly_salary'] ?? 0;
        $workingDaysPerMonth = $employeeData['working_days_per_month'] ?? 22;
        $workingHoursPerDay = $employeeData['working_hours_per_day'] ?? 8;

        // Use rounded daily rate for all calculations
        $dailyRate = round($monthlySalary / $workingDaysPerMonth, 2);
        $hourlyRate = round($dailyRate / $workingHoursPerDay, 2);

        // 1. Basic Salary - calculate based on days worked (cutoff period)
        // Daily Rate × Days Worked = Cutoff Pay
        $daysWorked = $attendance['days_worked'] ?? 0;
        $basicSalary = round($dailyRate * $daysWorked, 2);

        // 2. Overtime Pay
        $overtimeHours = $attendance['overtime_hours'] ?? 0;
        $overtimePay = round($hourlyRate * 1.25 * $overtimeHours, 2);

        // 3. Holiday Pay (daily rate × holiday days)
        // Support both 'holiday_days' and 'regular_holiday_worked' for compatibility
        $holidayDays = $attendance['holiday_days'] ?? ($attendance['regular_holiday_worked'] ?? 0);
        $holidayPay = round($dailyRate * 2 * $holidayDays, 2);

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
        $grossPay = $basicSalary + $overtimePay + $holidayPay + $nightDiff + $totalAllowances + $totalBenefits + $manualAdd - $manualSubtract - $lateDeduction;
        $grossPay = round($grossPay, 2);

        // 9. Deductions
        $totalDeductions = round(array_sum($deductions), 2);

        // 10. Net Pay
        $netPay = $preview ? $grossPay : round($grossPay - $totalDeductions, 2);

        return [
            'monthly_salary' => round($monthlySalary, 2),
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
            'basic_salary' => $basicSalary,
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
}
