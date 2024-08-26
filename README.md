# Kpi for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/elegantengineeringtech/laravel-kpi/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/elegantengineeringtech/laravel-kpi/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/elegantly/laravel-kpi.svg?style=flat-square)](https://packagist.org/packages/elegantly/laravel-kpi)

KPI for your Laravel App

## Installation

You can install the package via composer:

```bash
composer require eleganlty/laravel-kpi
```

You can publish and run the migrations with:

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
];
```

## Usage

```php
$kpi = new Elegantly\Kpi();
echo $kpi->echoPhrase('Hello, Elegantly!');
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
