<?php

namespace Elegantly\Kpi;

use Elegantly\Kpi\Commands\SnapshotKpiCommand;
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
            ->hasCommand(SnapshotKpiCommand::class);
    }
}
