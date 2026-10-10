<?php

namespace Nuvis\FijiPayroll;

use Illuminate\Support\ServiceProvider;
use Nuvis\FijiPayroll\Services\PayeCalculator;
use Nuvis\FijiPayroll\Services\FnpfService;
use Nuvis\FijiPayroll\Services\PayrollCalculator;
use Nuvis\FijiPayroll\Services\PayrollProcessor;
use Nuvis\FijiPayroll\Services\Exports\FrcsTposExporter;
use Nuvis\FijiPayroll\Services\Exports\Banks\BankFormatterManager;

class FijiPayrollServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/fiji-payroll.php', 'fiji-payroll');

        $this->app->singleton(PayeCalculator::class);
        $this->app->singleton(FnpfService::class);
        $this->app->singleton(PayrollCalculator::class);
        $this->app->singleton(PayrollProcessor::class);
        $this->app->singleton(FrcsTposExporter::class);
        $this->app->singleton(BankFormatterManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fiji-payroll');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/fiji-payroll.php' => config_path('fiji-payroll.php'),
            ], 'fiji-payroll-config');
        }
    }
}
