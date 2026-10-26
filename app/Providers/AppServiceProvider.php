<?php

namespace App\Providers;

use App\Models\db\Karyawan;
use App\Observers\KaryawanAttendanceObserver;
use App\Services\BpjsHealthMinimumService;
use App\Services\PayrollAttendanceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(BpjsHealthMinimumService::class);
        $this->app->scoped(PayrollAttendanceService::class);
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
