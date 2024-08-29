<?php

use Brick\Money\Money;
use Elegantly\Kpi\Models\Kpi;

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
            'money_value' => 10000,
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
