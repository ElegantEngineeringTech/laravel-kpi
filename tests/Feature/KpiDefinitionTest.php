<?php

declare(strict_types=1);

use Carbon\CarbonPeriod;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiValue;
use Elegantly\Kpi\Models\Kpi;
use Elegantly\Kpi\Tests\TestKpiDefinition;

it('can seed kpis between the right dates', function (KpiInterval $interval) {

    $start = $interval->toStartOf()->sub($interval->value, 0);
    $end = $interval->toEndOf();

    $seeded = TestKpiDefinition::seed(
        start: $start,
        end: $end,
        interval: "1 {$interval->toSmallerUnit()}"
    );

    expect(
        $seeded->count()
    )->toBe(
        (int) round($start->diffInUnit($interval->toSmallerUnit(), $end))
    );
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query the sum of all kpis per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        start: $interval->toStartOf()->sub($interval->value, 9),
        end: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => round($kpis->sum(fn (Kpi $kpi) => $kpi->number_value), 2));

    $sum = TestKpiDefinition::sum(
        interval: $interval
    )
        ->keyBy(fn (KpiValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiValue $value) => (float) round($value->value, 2));

    expect(
        $kpis->toArray()
    )->toBe(
        $sum->toArray()
    );
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query the avg of all kpis per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        start: $interval->toStartOf()->sub($interval->value, 9),
        end: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => round($kpis->average(fn (Kpi $kpi) => $kpi->number_value), 4));

    $avg = TestKpiDefinition::avg(
        interval: $interval
    )
        ->keyBy(fn (KpiValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiValue $value) => round($value->value, 4));

    expect(
        $kpis->toArray()
    )->toBe(
        $avg->toArray()
    );
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query the max of all kpis per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        start: $interval->toStartOf()->sub($interval->value, 9),
        end: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => $kpis->max(fn (Kpi $kpi) => $kpi->number_value));

    $max = TestKpiDefinition::max(
        interval: $interval
    )
        ->keyBy(fn (KpiValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiValue $value) => (float) $value->value);

    expect(
        $kpis->toArray()
    )->toBe(
        $max->toArray()
    );
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query the min of all kpis per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        start: $interval->toStartOf()->sub($interval->value, 9),
        end: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => $kpis->min(fn (Kpi $kpi) => $kpi->number_value));

    $min = TestKpiDefinition::min(
        interval: $interval
    )
        ->keyBy(fn (KpiValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiValue $value) => (float) $value->value);

    expect(
        $kpis->toArray()
    )->toBe(
        $min->toArray()
    );
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query the count of all kpis per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        start: $interval->toStartOf()->sub($interval->value, 9),
        end: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => $kpis->count());

    $count = TestKpiDefinition::count(
        interval: $interval
    )
        ->keyBy(fn (KpiValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiValue $value) => $value->value);

    expect(
        $kpis->toArray()
    )->toBe(
        $count->toArray()
    );
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query and map to a period all kpis per interval', function (KpiInterval $interval) {

    $units = 1;
    $start = $interval->toStartOf()->sub($interval->toUnit(), $units);
    $end = $interval->toEndOf();

    $seeded = TestKpiDefinition::seed(
        start: $start,
        end: $end,
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $period = CarbonPeriod::between(
        start: $start,
        end: $end,
    )->interval($interval->toCarbonInterval());

    $kpis = TestKpiDefinition::getPeriod(
        start: $start,
        end: $end,
        interval: $interval
    );

    expect($kpis)->toHaveLength($period->count());
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);

it('can query and map to a period all kpis differences per interval', function (KpiInterval $interval) {

    $units = 1;
    $start = $interval->toStartOf()->sub($interval->toUnit(), $units);
    $end = $interval->toEndOf();

    $seeded = TestKpiDefinition::seed(
        start: $start,
        end: $end,
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $period = CarbonPeriod::between(
        start: $start,
        end: $end,
    )->interval($interval->toCarbonInterval());

    $kpis = TestKpiDefinition::getDiffPeriod(
        start: $start,
        end: $end,
        interval: $interval
    );

    expect($kpis)->toHaveLength($period->count());
})->with([
    [KpiInterval::Minute],
    [KpiInterval::Hour],
    [KpiInterval::Day],
    [KpiInterval::Month],
    [KpiInterval::Year],
]);
