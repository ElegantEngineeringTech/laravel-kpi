<?php

declare(strict_types=1);

use Brick\Money\Money;
use Elegantly\Kpi\KpiMoneyDefinition;
use Elegantly\Kpi\Models\Kpi;

it('can calculate the difference between two money KPI', function () {
    $old = new Kpi([
        'value' => Money::of(100, 'USD'),
    ]);

    $new = new Kpi([
        'value' => Money::of(200, 'USD'),
    ]);

    $diff = KpiMoneyDefinition::diff($old, $new);

    expect(
        $diff?->getAmount()->toFloat()
    )->toBe(
        100.0
    );
});
