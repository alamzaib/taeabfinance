<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Payment;
use App\Models\User;
use App\Observers\PaymentObserver;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        User::observe(UserObserver::class);
    }
}
