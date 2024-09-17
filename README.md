# Store, analyse and retrieve KPI over time in your Laravel App

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)

This package provides a way to store kpis from your app in your database and then retreive them easily in different ways. It is espacially usefull to tracks things related to your models like:

-   the number of users
-   the number of subscribed users
-   the total revenue ...

It's a perfect tool for building dashboard ans display stats and charts.

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

## Concepts

This package is not a query builder, it's based on a `kpis` table where you will store all your kpis. With this approach, your kpis from the past (like the number of users you had a year ago) will not be altered if you permanently delete a model.

Retreiving kpis will also be much more efficient when asking for computed values that often require join like "users who have purchased last week" for example.

A Kpi could be simple or complex, here is some examples:

-   The number of registerd users
-   The number of active users each month
-   The total amount invoiced to customer
-   The number of recurring customers
-   The Average Order Value
-   ...

A Kpi can be "absolute" or "relative":

-   An absolute Kpi is a Kpi representing the state of the world like the number of users for exemple.
-   A relative Kpi is a Kpi representing a variation of the world like the number of new users each day for exemple.

Sometimes the most relevant Kpi is the relative one and sometimes it's the absolute one. This really depends on the context.

99% of the time you can extract the "relative" Kpi form the "absolute" one and vice versa.
This is wy I would recommand you to store you Kpi in only the "absolute" way and compute the relative value when needed.

## Usage

A Kpi is represented by two things:

-   A definition
-   Its values

The definition is a simple class extending `KpiDefinition` where you can configure everything.

Each definition must have a unique `name` like "users:count" for exemple.

The values are stored in the `kpis` table and are represented by the `Kpi` model.

A Kpi value can have:

-   A value: float, string, Money, json.
-   A description: string (optional)
-   Tags: array of strings (optional)
-   Metadata: json (optional)
-   A date: Datetime

### 1. Defining a KPI

As said above, you will have to store your kpis in the database.

A single KPI is represented by a single `KpiDefinition` class, to make the experience smoother the package provides a class for each type:

-   `KpiFloatDefinition`
-   `KpiStringDefinition`
-   `KpiMoneyDefinition`
-   `KpiJsonDefinition`

but you could also use `KpiDefinition` if you want or need.

```php
namespace App\Kpis\Users;

use App\Models\User;
use Elegantly\Kpi\Contracts\HasDifference;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiFloatDefinition;

class UsersCountKpi extends KpiFloatDefinition
{
    public static function getName(): string
    {
        return 'users:count';
    }

    /**
     * This Kpi is intended to be snapshotted every Day
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

As you can see that the `KpiDefinition` class have one property: `date`. The property represent the theorical date of the snapshot.
When you can compute your KPI in the past, try to use `date`, this will allow you to seed your KPIs in the past.

### 2. Snapotting your KPIs

There are two ways to snapshot a KPI:

-   Schedule the `kpis:snapshot` command
-   Manual snaphot

#### Using the command and the scheduler

This package assumes that you want to snapshot every KPI at a regular interval.
For exemple: capturing the number of active user every minute.

To do that, you should schedule the command `kpis:snapshot` to run at regular interval in your app.

Every KPI are not always snashotted at the same interval so you will need to schedule the command multiple times:

```php
$schedule->command(SnapshotKpisCommand::class, [
    'interval'=> KpiInteval::Hour
])->everyHour();

$schedule->command(SnapshotKpisCommand::class, [
    'interval'=> KpiInteval::Day
])->hourly();
```

#### Manually

You can snapshot your KPI manually using the `snapshot` method

```php
use App\Kpis\Users\UsersCountKpi;

UsersCountKpi::snapshot(now());
```

### 3. Seeding your KPIs

#### Seeding with the command

When adding a KPI to an existing project, you might want to seed your KPIs in the past.

To allow you to do that, the `KpiDefinition` class have one property `date`.
This property represent the datetime of the snapshot. When possible, you should use it in the `getValue` method to compute your KPI.

When your KPIs are configured with the `date`, you can run the following command:

```bash
php artisan kpis:seed "one year ago" "now"
```

#### Seeding manually

you can seed your KPI manually using the `seed` method

```php
use App\Kpis\Users\UsersCountKpi;

UsersCountKpi::seed(
    from: now()->subYear(),
    to: now(),
    interval: KpiInterval::Day
);
```

### 3. Querying your KPIs

Most of the time, you will want to query the KPIs to visualize them in a chart or a dashboard.

To help you creating insightful charts quickly, the `KpiDefinition` class provides various methods:

```php
use App\Kpis\Users\UsersCountKpi;

/**
 * Will return a collection of Kpi with only one Kpi per interval keyed by the date
 */
UsersCountKpi::getPeriod(
    start: now()->subDays(6),
    end: now(),
    interval: KpiInterval::Day
);

/**
 * Will return a collection of KpiValue representing the "relative" Kpi (ei the difference between consequtive Kpi)
 * This is usefull for "absolute" Kpis such as the users count.
 */
UsersCountKpi::getDiffPeriod(
    start: now()->subDays(6),
    end: now(),
    interval: KpiInterval::Day
);


```

### 4. Aggregating your KPIs

```php
/**
 * The will return the Kpi where the value is the max for each month
 * This is usefull for "relative" Kpis where you could want to see the local maximum
 */
UsersCountKpi::max(
    start: now()->subMonths(6),
    end: now(),
    interval: KpiInterval::Month
);

UsersCountKpi::min(
    start: now()->subMonths(6),
    end: now(),
    interval: KpiInterval::Month
);

UsersCountKpi::avg(
    start: now()->subMonths(6),
    end: now(),
    interval: KpiInterval::Month
);

UsersCountKpi::sum(
    start: now()->subMonths(6),
    end: now(),
    interval: KpiInterval::Month
);

UsersCountKpi::count(
    start: now()->subMonths(6),
    end: now(),
    interval: KpiInterval::Month
);
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
