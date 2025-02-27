<?php

namespace Elegantly\Kpi;

use Elegantly\Kpi\Commands\KpisSeedCommand;
use Elegantly\Kpi\Commands\KpisSnapshotCommand;
use Elegantly\Kpi\Contracts\KpiModelInterface;
use Elegantly\Kpi\Models\Kpi;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class KpiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-kpi')
            ->hasConfigFile()
            ->hasMigration('create_kpis_table')
            ->hasCommand(KpisSnapshotCommand::class)
            ->hasCommand(KpisSeedCommand::class);
    }

    public function register()
    {
        $customModelClass = config('kpi.model', Kpi::class);
        if ($customModelClass) {
            $this->app->bind(KpiModelInterface::class, function ($app) use ($customModelClass) {
                return $app->make($customModelClass);
            });
        }

        parent::register();
    }
}
