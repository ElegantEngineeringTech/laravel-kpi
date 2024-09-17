# Store, Analyze, and Retrieve KPIs Over Time in Your Laravel App

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)

This package provides an easy way to store KPIs from your application in your database and retrieve them in various formats. It's especially useful for tracking data related to your models, such as:

-   Number of users
-   Number of subscribed users
-   Total revenue
-   And more...

It's a perfect tool for building dashboards and displaying stats and charts.

## Installation

Install the package via Composer:

```bash
composer require elegantly/laravel-kpi
```

Publish and run the migrations with:

```bash
php artisan vendor:publish --tag="kpi-migrations"
php artisan migrate
```

Publish the configuration file with:

```bash
php artisan vendor:publish --tag="kpi-config"
```

Here is the content of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Discover Definitions
    |--------------------------------------------------------------------------
    |
    | If 'enabled' is set to true, your KPI definitions will be automatically
    | discovered when taking snapshots.
    | Set the 'path' to specify the directory where your KPI definitions are stored.
    | Definitions will be discovered from this path and its subdirectories.
    |
    */
    'discover' => [
        'enabled' => true,
        /**
         * This path will be used with the `app_path` helper, like `app_path('Kpis')`.
         */
        'path' => 'Kpis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Definitions
    |--------------------------------------------------------------------------
    |
    | You can manually register your KPI definitions if you are not using
    | "discover" or if you want to add additional definitions located elsewhere.
    |
    */
    'definitions' => [],
];
```

## Concepts

This package is not a query builder. Instead, it is based on a `kpis` table where all KPIs are stored. This allows historical data (e.g., the number of users a year ago) to remain intact, even if models are permanently deleted.

Retrieving KPIs is also way more efficient when calculating complex values, such as "users who made a purchase last week."

A KPI can be simple or complex. Examples include:

-   Number of registered users
-   Monthly active users
-   Total revenue invoiced to customers
-   Number of recurring customers
-   Average Order Value
-   ...

KPIs can be either "absolute" or "relative":

-   An **absolute KPI** represents a current state, such as the total number of users.
-   A **relative KPI** represents a change, such as the number of new users each day.

Depending on the context, either an absolute or relative KPI may be more relevant. In most cases, relative KPIs can be derived from absolute ones, and vice versa. Therefore, it's often recommended to store KPIs as "absolute" and compute relative values when needed.

## Usage

A KPI consists of two key components:

-   A **definition**
-   Its **values**

The **definition** is a class extending `KpiDefinition`, where you configure the KPI.

Each KPI definition must have a unique `name`, such as `users:count`.

The **values** are stored in the `kpis` table and represented by the `Kpi` model.

A KPI value may contain:

-   A value (float, string, Money, JSON)
-   A description (optional)
-   Tags (optional)
-   Metadata (optional)
-   A timestamp

### 1. Defining a KPI

Each KPI is represented by a single `KpiDefinition` class. The package offers predefined classes for each data type:

-   `KpiFloatDefinition`
-   `KpiStringDefinition`
-   `KpiMoneyDefinition`
-   `KpiJsonDefinition`

You can also extend `KpiDefinition` if you need custom behavior.

Example:

```php
namespace App\Kpis\Users;

use App\Models\User;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiFloatDefinition;

class UsersCountKpi extends KpiFloatDefinition
{
    public static function getName(): string
    {
        return 'users:count';
    }

    /**
     * This KPI is intended to be snapshotted every day.
     */
    public static function getSnapshotInterval(): KpiInterval
    {
        return KpiInterval::Day;
    }

    public function getValue(): float
    {
        return (float) User::query()
            ->when($this->date, fn ($query) => $query->where('created_at', '<=', $this->date))
            ->toBase()
            ->count();
    }

    /**
     * Description to store alongside the KPI value
     */
    public function getDescription(): ?string
    {
        return null;
    }

    /**
     * Tags to store alongside the KPI value
     */
    public function getTags(): ?array
    {
        return null;
    }

    /**
     * Metadata to store alongside the KPI value
     */
    public function getMetadata(): ?array
    {
        return null;
    }
}
```

As shown, the `KpiDefinition` class has a `date` property, representing the snapshot date. When possible, use `date` in `getValue`, this will allow you to seed your KPIs with past data.

### 2. Snapshotting KPIs

There are two ways to create KPI snapshots:

-   Schedule the `kpis:snapshot` command
-   Manually create snapshots

#### Using the Command and Scheduler

To capture KPI data at regular intervals (e.g., hourly or daily), schedule the `kpis:snapshot` command in your application's scheduler.

Example:

```php
$schedule->command(SnapshotKpisCommand::class, [
    'interval' => KpiInterval::Hour,
])->everyHour();

$schedule->command(SnapshotKpisCommand::class, [
    'interval' => KpiInterval::Day,
])->daily();
```

#### Manual Snapshot

You can manually snapshot a KPI using the `snapshot` method:

```php
use App\Kpis\Users\UsersCountKpi;

UsersCountKpi::snapshot(
    date: now()
);
```

### 3. Seeding KPIs

#### Seeding with the Command

When adding KPIs to an existing project, you may want to seed past data. If your `KpiDefinition` class supports the `date` property, you can seed KPIs using the following command:

```bash
php artisan kpis:seed "one year ago" "now"
```

#### Manual Seeding

You can also seed KPIs manually using the `seed` method:

```php
use App\Kpis\Users\UsersCountKpi;

UsersCountKpi::seed(
    from: now()->subYear(),
    to: now(),
    interval: KpiInterval::Day
);
```

### 4. Querying KPIs

To visualize KPIs in charts or dashboards, the `KpiDefinition` class provides several helper methods:

```php
use App\Kpis\Users\UsersCountKpi;

/**
 * Retrieve a collection of KPIs for a given period, keyed by date.
 */
UsersCountKpi::getPeriod(
    start: now()->subDays(6),
    end: now(),
    interval: KpiInterval::Day
);

/**
 * Retrieve a collection of relative KPIs (i.e., the difference between consecutive snapshots).
 */
UsersCountKpi::getDiffPeriod(
    start: now()->subDays(6),
    end: now(),
    interval: KpiInterval::Day
);
```

### 5. Aggregating KPIs

You can easily aggregate KPIs using the following methods:

```php
/**
 * Retrieve the KPI with the maximum value for each month.
 */
UsersCountKpi::max(
    start: now()->subMonths(6),
    end: now(),
    interval: KpiInterval::Month
);

UsersCountKpi::min(...);
UsersCountKpi::avg(...);
UsersCountKpi::sum(...);
UsersCountKpi::count(...);
```

## Filament Plugin

Display your KPIs in a beatiful way with 1 line using our filament plugin: [`elegantly/filament-kpi`](https://github.com/ElegantEngineeringTech/filament-kpi)

## Testing

Run the tests with:

```bash
composer test
```

## Changelog

For recent changes, see the [CHANGELOG](CHANGELOG.md).

## Contributing

For contribution guidelines, see [CONTRIBUTING](CONTRIBUTING.md).

## Security Vulnerabilities

For details on reporting security vulnerabilities, review [our security policy](../../security/policy).

## Credits

-   [Quentin Gabriele](https://github.com/QuentinGab)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
