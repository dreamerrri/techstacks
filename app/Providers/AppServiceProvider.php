<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \DB::listen(fn($q) => \Log::info($q->sql));
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}