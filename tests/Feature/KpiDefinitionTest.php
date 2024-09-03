<?php

use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiAggregatedValue;
use Elegantly\Kpi\Models\Kpi;
use Elegantly\Kpi\Tests\TestKpiDefinition;

it('can seed kpis between the right dates', function (KpiInterval $interval) {

    $from = $interval->toStartOf()->sub($interval->value, 0);
    $to = $interval->toEndOf();

    $seeded = TestKpiDefinition::seed(
        from: $from,
        to: $to,
        interval: "1 {$interval->toSmallerUnit()}"
    );

    expect(
        $seeded->count()
    )->toBe(
        (int) round($from->diffInUnit($interval->toSmallerUnit(), $to))
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
        from: $interval->toStartOf()->sub($interval->value, 9),
        to: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => $kpis->sum(fn (Kpi $kpi) => $kpi->number_value));

    $sum = TestKpiDefinition::sum(
        interval: $interval
    )
        ->keyBy(fn (KpiAggregatedValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiAggregatedValue $value) => (float) $value->value);

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
        from: $interval->toStartOf()->sub($interval->value, 9),
        to: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => $kpis->average(fn (Kpi $kpi) => $kpi->number_value));

    $avg = TestKpiDefinition::avg(
        interval: $interval
    )
        ->keyBy(fn (KpiAggregatedValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiAggregatedValue $value) => (float) $value->value);

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

it('can query the count of all kpis per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        from: $interval->toStartOf()->sub($interval->value, 9),
        to: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $kpis = TestKpiDefinition::query()
        ->get()
        ->groupBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()))
        ->map(fn ($kpis) => $kpis->count());

    $count = TestKpiDefinition::count(
        interval: $interval
    )
        ->keyBy(fn (KpiAggregatedValue $value) => $value->date->format($interval->toDateFormat()))
        ->map(fn (KpiAggregatedValue $value) => $value->value);

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
