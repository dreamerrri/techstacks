<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\Transport\MailtrapApiTransport;
use Illuminate\Support\Facades\Mail;    
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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

        Mail::extend('mailtrap-api', function (array $config = []) {
        return new MailtrapApiTransport(
            apiToken: $config['api_token'],
            host: $config['host'],
            inboxId: $config['inbox_id'] ?? null,
        );
    });


        Employee::observe(EmployeeObserver::class);
       //Doesnt exist yet add in future  Attendance::observe(AttendanceObserver::class);
        PayrollPeriod::observe(PayrollPeriodObserver::class);
        PayrollInput::observe(PayrollInputObserver::class);
        Allowance::observe(AllowanceObserver::class);
        Benefit::observe(BenefitObserver::class);
        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
    


 
    ResetPassword::toMailUsing(function ($notifiable, $token) {
        $resetUrl = url(route('updatePassword', [
    'token' => $token,
    'email' => $notifiable->getEmailForPasswordReset(),
], false));

        return (new MailMessage)
            ->subject('Reset Your LogiPay Password')
            ->view('emails.reset-password', [
                'resetUrl' => $resetUrl,
                'email'    => $notifiable->getEmailForPasswordReset(),
                'name'     => $notifiable->name ?? null,
                'expireMinutes' => config('auth.passwords.users.expire', 60),
            ]);
    });
}
}


