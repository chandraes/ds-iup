<?php

namespace App\Providers;

use App\Models\db\Karyawan;
use App\Observers\KaryawanAttendanceObserver;
use Illuminate\Support\ServiceProvider;

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
        Karyawan::observe(KaryawanAttendanceObserver::class);
        //
    }
}
