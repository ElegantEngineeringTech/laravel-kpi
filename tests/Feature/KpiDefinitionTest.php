<?php

use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiAggregatedValue;
use Elegantly\Kpi\Models\Kpi;
use Elegantly\Kpi\Tests\TestKpiDefinition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

it('can query 1 kpi per interval', function (KpiInterval $interval) {

    $seeded = TestKpiDefinition::seed(
        from: $interval->toStartOf()->sub($interval->value, 9),
        to: $interval->toEndOf(),
        interval: "1 {$interval->toSmallerUnit()}"
    );

    $query = TestKpiDefinition::query()->latestPerInterval($interval);

    /** @var Collection<int, Kpi> $kpis */
    $kpis = $query->get();

    $kpisCountBy = $kpis->countBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()));

    expect($kpisCountBy->every(fn ($value) => $value === 1))->toBeTrue();

    // seems to work
    // $sql = DB::select(
    //     'SELECT *
    //     FROM (
    //         SELECT
    //             kpis.*,
    //             ROW_NUMBER() OVER (PARTITION BY DATE(created_at) ORDER BY created_at DESC) as row_num
    //         FROM kpis
    //     ) as ranked_kpis
    //     WHERE row_num = 1
    //     ORDER BY created_at DESC;'
    // );

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
        ->map(fn ($kpis) => $kpis->sum(fn (Kpi $kpi) => (float) $kpi->number_value));

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
