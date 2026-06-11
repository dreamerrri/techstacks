<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{Employee, Attendance, PayrollPeriod, PayrollInput, Allowance, Benefit, User, Role};
use App\Observers\{EmployeeObserver, AttendanceObserver, PayrollPeriodObserver, PayrollInputObserver, AllowanceObserver, BenefitObserver, UserObserver, RoleObserver};

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // sigghhhhanep
    }

    public function boot(): void
    {
    
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }


        Employee::observe(EmployeeObserver::class);
       //Doesnt exist yet add in future  Attendance::observe(AttendanceObserver::class);
        PayrollPeriod::observe(PayrollPeriodObserver::class);
        PayrollInput::observe(PayrollInputObserver::class);
        Allowance::observe(AllowanceObserver::class);
        Benefit::observe(BenefitObserver::class);
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
    }
}