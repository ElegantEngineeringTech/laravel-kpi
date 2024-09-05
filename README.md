# Store, analyse and retrieve KPI over time in your Laravel App

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)

This package provides a way to store kpis from your app in your database and then retreive them easily in different ways. It is espacially usefull to tracks things related to your models like:

-   the number of users
-   the number of subscribed users
-   the total revenue ...

It's a perfect tool for building dashboard ans display stats/charts.

## Installation

You can install the package via composer:

```bash
composer require elegantly/laravel-kpi
```

You should publish and run the migrations with:

```bash
php artisan vendor:publish --tag="kpi-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="kpi-config"
```

This is the contents of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Discover Definitions
    |--------------------------------------------------------------------------
    |
    | If enabled is set to true, your KPI definitions will be automatically discovered when taking snapshot.
    | Customize the path to indicate the directory where your definitions are located in your app.
    | The KPI definitions will be discovered from the path and its subdirectories
    |
    */
    'discover' => [
        'enabled' => true,
        /**
         * This path will be used with `app_path` helper like `app_path('Kpis')`
         */
        'path' => 'Kpis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Definitions
    |--------------------------------------------------------------------------
    |
    | You can manually register your kpi definitions if you are not using "auto-discover"
    | or if you want to add more deifnitions not stored in the main path
    |
    */
    'definitions' => [],
];
```

## Usage

This package is not a query builder, it's based on a `kpis` table where you will store all your kpis. With this approach, your kpis from the past (like the number of users you had a year ago) will not be altered if you permanently delete a model.

Retreiving kpis will also be much more efficient when asking for computed values that often require join like "users who have purchased last week" for example.

### 1. Defining a KPI

As said above, you will have to store the kpis you need in the database.

A KPI is represented by a `KpiDefinition` class.

### 2. Snapotting your KPIs

There are two ways to snapshot a KPI:

-   Schedule the `kpis:snapshot` command
-   Manual snaphot

This package assumes that you want to snapshot every KPI at a regular interval.
For exemple: capturing the number of active user every minute.

To do that, you should schedule the command `kpis:snapshot` to run at regular interval in your app.

Every KPI are not always snashotted at the same interval so you will need to schedule the command multiple times:

```php
$schedule->command(SnapshotKpisCommand::class, [
    'interval'=> KpiInteval::Minute
])->everyMinute();

$schedule->command(SnapshotKpisCommand::class, [
    'interval'=> KpiInteval::Hour
])->hourly();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Quentin Gabriele](https://github.com/40128136+QuentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
