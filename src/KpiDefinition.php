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
     * @return null|string|int|float|ArrayObject<int|string, mixed>
     */
    abstract public function getValue(): null|string|int|float|ArrayObject;

    /**
     * Tags to store alongside the KPI value
     *
     * @return string[]
     */
    abstract public function getTags(): ?array;

    /**
     * Metadata to store alongside the KPI value
     *
     * @return null|array<string|int, mixed>
     */
    abstract public function getMetadata(): ?array;

    /**
     * Description to store alongside the KPI value
     */
    abstract public function getDescription(): ?string;

    /**
     * Unique name like "users:active:count"
     */
    abstract public static function getName(): string;

    /**
     * Display name like "Active Users"
     * The default value is the KPI's name
     */
    public static function getLabel(): string
    {
        return static::getName();
    }

    /**
     * The interval at which the KPI will be captured using the kpi:snapshot command.
     */
    abstract public static function getSnapshotInterval(): KpiInterval;

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

    public static function create(Carbon $date): Kpi
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
            $kpis->push(static::create($date));
        }

        return $kpis;
    }
}
