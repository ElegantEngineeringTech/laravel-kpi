<?php

namespace Elegantly\Kpi;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Elegantly\Kpi\Enums\KpiAggregate;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\Models\Kpi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

abstract class KpiDefinition
{
    final public function __construct(
        public Carbon $date
    ) {
        //
    }

    /**
     * The interval at which the KPI will be captured using the kpi:snapshot command.
     */
    abstract public static function getSnapshotInterval(): KpiInterval;

    /**
     * Unique name like "users:active:count"
     */
    abstract public static function getName(): string;

    /**
     * @return null|string|int|float|ArrayObject<int|string, mixed>
     */
    abstract public function getValue(): null|string|int|float|ArrayObject;

    /**
     * Display name like "Active Users"
     * The default value is the KPI's name
     */
    public static function getLabel(): string
    {
        return static::getName();
    }

    /**
     * Tags to store alongside the KPI value
     *
     * @return string[]
     */
    public function getTags(): ?array
    {
        return null;
    }

    /**
     * Metadata to store alongside the KPI value
     *
     * @return null|array<string|int, mixed>
     */
    public function getMetadata(): ?array
    {
        return null;
    }

    /**
     * Description to store alongside the KPI value
     */
    public function getDescription(): ?string
    {
        return null;
    }

    /**
     * @return Builder<Kpi>
     */
    public static function query(
        ?Carbon $from = null,
        ?Carbon $to = null,
    ): mixed {

        $query = Kpi::query()->where('name', static::getName());

        if ($from) {
            $query->where('date', '>=', $from);
        }
        if ($to) {
            $query->where('date', '<=', $to);
        }

        return $query;
    }

    /**
     * @param  Builder<Kpi>  $query
     * @return SupportCollection<string, Kpi|null>
     */
    public static function toPeriod(
        Carbon $start,
        Carbon $end,
        KpiInterval $interval,
        ?Builder $query = null,
    ): mixed {

        $query ??= static::query();

        $period = $interval->toPeriod(
            start: $start,
            end: $end
        );

        $kpis = $query
            ->where('date', '>=', $period->getStartDate())
            ->where('date', '<=', $period->getEndDate())
            ->latestPerInterval($interval)
            ->get()
            ->keyBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()));

        $results = new SupportCollection;

        /**
         * @var Carbon $date
         */
        foreach ($period as $date) {
            $key = $date->format($interval->toDateFormat());
            $results->put(
                $key,
                $kpis->get($key) ?? null
            );
        }

        return $results;
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int|float : SupportCollection<int, KpiAggregatedValue<int|float>>)
     */
    public static function max(
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Max,
            query: $query,
            column: $column,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int|float : SupportCollection<int, KpiAggregatedValue<int|float>>)
     */
    public static function min(
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Min,
            query: $query,
            column: $column,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int|float : SupportCollection<int, KpiAggregatedValue<int|float>>)
     */
    public static function sum(
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Sum,
            query: $query,
            column: $column,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int|float : SupportCollection<int, KpiAggregatedValue<int|float>>)
     */
    public static function avg(
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Average,
            query: $query,
            column: $column,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int : SupportCollection<int, KpiAggregatedValue<int>>)
     */
    public static function count(
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|SupportCollection {
        /**
         * @var int|SupportCollection<int, KpiAggregatedValue<int>>
         */
        return static::aggregate(
            aggregate: KpiAggregate::Count,
            query: $query,
            column: $column,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int|float : SupportCollection<int, KpiAggregatedValue<int|float>>)
     */
    public static function aggregate(
        KpiAggregate $aggregate,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        $query ??= static::query();

        if ($interval) {

            $grammar = $query->getQuery()->getGrammar();

            /**
             * @var SupportCollection<int, object{ date_group: string, aggregated_value: int|float }> $results
             */
            $results = $query
                ->toBase()
                ->selectRaw("{$interval->toSqlFormat($grammar::class, 'date')} as date_group")
                ->groupBy('date_group')
                ->addSelect($aggregate->toSqlSelect($column, 'aggregated_value'))
                ->orderBy('date_group')
                ->get();

            return $results->map(fn (object $result) => new KpiAggregatedValue(
                date: $interval->fromDateFormat($result->date_group) ?: now(),
                value: $result->aggregated_value,
            ));
        }

        /**
         * @var int|float
         */
        return $query
            ->toBase()
            ->aggregate(
                function: $aggregate->toBuilderFunction(),
                columns: [$column]
            );
    }

    public static function snapshot(Carbon $date): Kpi
    {
        $definition = new static($date);

        $kpi = new Kpi;

        $kpi
            ->setName(static::getName())
            ->setValue($definition->getValue())
            ->setDate($date->clone())
            ->setMetadata($definition->getMetadata())
            ->setDescription($definition->getDescription())
            ->setTags($definition->getTags())
            ->save();

        return $kpi;
    }

    /**
     * @return Collection<int, Kpi>
     */
    public static function seed(
        Carbon $from,
        Carbon $to,
        KpiInterval|string $interval,
    ): Collection {

        /**
         * @var CarbonPeriod $period
         */
        $period = CarbonPeriod::between(
            start: $from->clone(),
            end: $to->clone(),
        )->interval(
            $interval instanceof KpiInterval ? $interval->toCarbonInterval() : $interval
        );

        /**
         * @var Collection<int, Kpi> $kpis
         */
        $kpis = new Collection;

        /**
         * @var Carbon $date
         */
        foreach ($period as $date) {
            $kpis->push(static::snapshot($date));
        }

        return $kpis;
    }
}
