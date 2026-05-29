<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Payroll\PayrollComputationEngine;

class PayrollComputationEngineTest extends TestCase
{
    public function testPayrollComputation()
    {
        $engine = new PayrollComputationEngine();
        $employeeData = [
            'daily_rate' => 1000,
            'hourly_rate' => 125,
        ];
        $attendance = [
            'days_worked' => 22,
            'overtime_hours' => 10,
            'holiday_hours' => 8,
            'night_hours' => 12,
        ];
        $deductions = [500, 200];
        $benefits = [300];
        $allowances = [1000, 500];

        $result = $engine->compute($employeeData, $attendance, $deductions, $benefits, $allowances);

        $this->assertEquals(22000, $result['basic_salary']);
        $this->assertEquals(1562.5, $result['overtime_pay']);
        $this->assertEquals(2000, $result['holiday_pay']);
        $this->assertEquals(150, $result['night_differential']);
        $this->assertEquals(1500, $result['allowances']);
        $this->assertEquals(300, $result['benefits']);
        $this->assertEquals(700, $result['deductions']);
        $this->assertEquals(27512.5, $result['gross_pay']);
        $this->assertEquals(26812.5, $result['net_pay']);
    }
}
