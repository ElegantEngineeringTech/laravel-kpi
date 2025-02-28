<?php

declare(strict_types=1);

namespace Elegantly\Kpi;

use Brick\Money\Money;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Elegantly\Kpi\Enums\KpiAggregate;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\Models\Kpi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @template TValue of null|float|string|Money|array<array-key, mixed>
 */
abstract class KpiDefinition
{
    /**
     * @param  ?CarbonInterface  $date  The date to snapshot
     */
    final public function __construct(
        public ?CarbonInterface $date = null
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
     * When possible, make sure the returned value is relative to the `date` property.
     * This will allow you to seed your KPI in the past. When seeding in the past in not possible
     * feel free to return any value you want like null or 0.
     *
     * @return TValue
     */
    abstract public function getValue(): null|float|string|Money|array;

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
     * @return null|array<int, scalar>
     */
    public function getTags(): ?array
    {
        return null;
    }

    /**
     * Metadata to store alongside the KPI value
     *
     * @return null|array<array-key, mixed>
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
     * @return Kpi<TValue>
     */
    public static function snapshot(?CarbonInterface $date = null): Kpi
    {
        $definition = new static($date);

        /**
         * @var Kpi<TValue> $kpi
         */
        $kpi = KpiServiceProvider::makeModelInstance();

        $date ??= now();

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
     * @return Collection<int, Kpi<TValue>>
     */
    public static function seed(
        CarbonInterface $start,
        CarbonInterface $end,
        KpiInterval|string $interval,
    ): Collection {

        /**
         * @var CarbonPeriod $period
         */
        $period = CarbonPeriod::between(
            start: $start->clone(),
            end: $end->clone(),
        )->interval(
            $interval instanceof KpiInterval ? $interval->toCarbonInterval() : $interval
        );

        /**
         * @var Collection<int, Kpi<TValue>> $kpis
         */
        $kpis = new Collection;

        /**
         * @var CarbonInterface $date
         */
        foreach ($period as $date) {
            $kpis->push(static::snapshot($date));
        }

        return $kpis;
    }

    /**
     * @return Builder<Kpi<TValue>>
     */
    public static function query(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
    ): mixed {

        /**
         * @var Builder<Kpi<TValue>>
         */
        $query = KpiServiceProvider::getModelClass()::query()->where('name', static::getName());

        if ($start) {
            $query->where('date', '>=', $start);
        }
        if ($end) {
            $query->where('date', '<=', $end);
        }

        return $query;
    }

    /**
     * Retreive the latest KPI difference on the given period at the given interval
     * Each value is the difference between the current and the previous value.
     * Exemple: The new users for each month from `1 year ago` to `now`.
     *
     * @param  Builder<Kpi>  $query
     * @return SupportCollection<string, KpiValue<mixed>>
     */
    public static function getDiffPeriod(
        CarbonInterface $start,
        CarbonInterface $end,
        KpiInterval $interval,
        ?Builder $query = null,
    ): mixed {

        $query ??= static::query();

        $period = $interval->toPeriod(
            start: $start,
            end: $end
        );

        /**
         * @var Collection<string, Kpi> $kpis
         */
        $kpis = static::latest(
            query: $query
                ->where(
                    'date',
                    '>=',
                    $period->getStartDate()->clone()->sub($interval->toUnit(), value: 1)
                )
                ->where('date', '<=', $period->getEndDate()),
            interval: $interval
        )->keyBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()));

        $results = new SupportCollection;

        /**
         * @var CarbonInterface $date
         */
        foreach ($period as $date) {
            $key = $date->format($interval->toDateFormat());

            $previousKey = $date->clone()
                ->sub($interval->toUnit(), value: 1)
                ->format($interval->toDateFormat());

            $value = static::diff($kpis->get($previousKey), $kpis->get($key));

            $results->put(
                $key,
                new KpiValue(
                    date: $date,
                    value: $value
                )
            );
        }

        return $results;
    }

    /**
     * Get the difference between two KPI values
     *
     * @param  Kpi<TValue>  $old
     * @param  Kpi<TValue>  $new
     * @return TValue
     */
    abstract public static function diff(?Kpi $old, ?Kpi $new): null|float|string|Money|array;

    /**
     * Retreive the latest KPI on the given period at the given interval
     * Exemple: The users count at each month from `1 year ago` to `now`.
     *
     * @param  Builder<Kpi>  $query
     * @return SupportCollection<string, null|Kpi<TValue>>
     */
    public static function getPeriod(
        CarbonInterface $start,
        CarbonInterface $end,
        KpiInterval $interval,
        ?Builder $query = null,
    ): SupportCollection {

        $query ??= static::query();

        $period = $interval->toPeriod(
            start: $start,
            end: $end
        );

        /**
         * @var Collection<string, Kpi> $kpis
         */
        $kpis = static::latest(
            query: $query
                ->where('date', '>=', $period->getStartDate())
                ->where('date', '<=', $period->getEndDate()),
            interval: $interval,
        )->keyBy(fn (Kpi $kpi) => $kpi->date->format($interval->toDateFormat()));

        $results = new SupportCollection;

        /**
         * @var CarbonInterface $date
         */
        foreach ($period as $date) {
            $key = $date->format($interval->toDateFormat());
            $results->put(
                $key,
                $kpis->get($key)
            );
        }

        return $results;
    }

    /**
     * Retreive the latest KPI at the given interval
     *
     * @param  Builder<Kpi>  $query
     * @return Collection<int, Kpi>
     */
    public static function latest(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        ?KpiInterval $interval = null,
    ): Collection {
        $query ??= static::query(
            start: $start,
            end: $end
        );

        if ($interval) {
            $grammar = $query->getQuery()->getGrammar();

            $subquery = static::query()
                ->toBase()
                ->selectRaw('MAX(id) AS max_id')
                ->groupByRaw($interval->toSqlFormat($grammar::class, 'date'));

            $table = KpiServiceProvider::makeModelInstance()->getTable();

            $query->toBase()->joinSub(
                query: $subquery,
                as: 'subquery',
                first: "{$table}.id",
                operator: '=',
                second: 'subquery.max_id'
            );

            return $query->latest('date')->get();
        }

        return $query->latest('date')->get();
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? int|float : SupportCollection<int, KpiValue<int|float>>)
     */
    public static function max(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Max,
            start: $start,
            end: $end,
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
     * @return (T is null ? int|float : SupportCollection<int, KpiValue<int|float>>)
     */
    public static function min(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Min,
            start: $start,
            end: $end,
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
     * @return (T is null ? int|float : SupportCollection<int, KpiValue<int|float>>)
     */
    public static function sum(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Sum,
            start: $start,
            end: $end,
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
     * @return (T is null ? int|float : SupportCollection<int, KpiValue<int|float>>)
     */
    public static function avg(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Average,
            start: $start,
            end: $end,
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
     * @return (T is null ? int : SupportCollection<int, KpiValue<int>>)
     */
    public static function count(
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|SupportCollection {
        /**
         * @var int|SupportCollection<int, KpiValue<int>>
         */
        return static::aggregate(
            aggregate: KpiAggregate::Count,
            start: $start,
            end: $end,
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
     * @return (T is null ? int|float : SupportCollection<int, KpiValue<int|float>>)
     */
    public static function aggregate(
        KpiAggregate $aggregate,
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
        ?Builder $query = null,
        string $column = 'number_value',
        ?KpiInterval $interval = null,
    ): int|float|SupportCollection {
        $query ??= static::query(
            start: $start,
            end: $end
        );

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

            return $results->map(fn (object $result) => new KpiValue(
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
}
