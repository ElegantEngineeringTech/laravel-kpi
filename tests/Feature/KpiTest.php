<?php

declare(strict_types=1);

use Brick\Money\Money;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\Models\Kpi;
use Elegantly\Kpi\Tests\TestKpiDefinition;

it('sets the right value column', function (mixed $value, array $expected) {
    $kpi = new Kpi;
    $kpi->setValue($value);

    expect($kpi->toArray())->toBe($expected);
})->with([
    [
        10.0,
        [
            'type' => 'number_value',
            'number_value' => 10.0,
            'json_value' => null,
            'string_value' => null,
            'money_value' => null,
            'money_currency' => null,
        ],
    ],
    [
        'foo',
        [
            'type' => 'string_value',
            'number_value' => null,
            'json_value' => null,
            'string_value' => 'foo',
            'money_value' => null,
            'money_currency' => null,
        ],
    ],
    [
        Money::ofMinor(10000, 'EUR'),
        [
            'type' => 'money_value',
            'number_value' => null,
            'json_value' => null,
            'string_value' => null,
            'money_value' => 100.00,
            'money_currency' => 'EUR',
        ],
    ],
    [
        ['foo' => 'bar'],
        [
            'type' => 'json_value',
            'number_value' => null,
            'json_value' => ['foo' => 'bar'],
            'string_value' => null,
            'money_value' => null,
            'money_currency' => null,
        ],
    ],
]);

it('can query 1 kpi per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        start: $interval->toStartOf()->sub($interval->value, 9),
        end: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::latest(interval: $interval);

    $kpisCountBy = $kpis->countBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()));

    expect($kpisCountBy->every(fn ($value) => $value === 1))->toBeTrue();
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);
