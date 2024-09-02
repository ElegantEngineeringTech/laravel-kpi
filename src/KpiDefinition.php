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
     * @return string[]
     */
    abstract public function getTags(): ?array;

    /**
     * @return null|array<string|int, mixed>
     */
    abstract public function getMetadata(): ?array;

    abstract public function getDescription(): ?string;

    abstract public static function getName(): string;

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
     * @return (T is null ? float : SupportCollection<int, KpiAggregatedValue>)
     */
    public static function sum(
        ?Builder $query = null,
        ?KpiInterval $interval = null,
    ): float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Sum,
            query: $query,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? float : SupportCollection<int, KpiAggregatedValue>)
     */
    public static function avg(
        ?Builder $query = null,
        ?KpiInterval $interval = null,
    ): float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Average,
            query: $query,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? float : SupportCollection<int, KpiAggregatedValue>)
     */
    public static function count(
        ?Builder $query = null,
        ?KpiInterval $interval = null,
    ): float|SupportCollection {
        return static::aggregate(
            aggregate: KpiAggregate::Count,
            query: $query,
            interval: $interval
        );
    }

    /**
     * @template T of null|KpiInterval
     *
     * @param  Builder<Kpi>  $query
     * @param  T  $interval
     * @return (T is null ? float : SupportCollection<int, KpiAggregatedValue>)
     */
    public static function aggregate(
        KpiAggregate $aggregate,
        ?Builder $query = null,
        ?KpiInterval $interval = null,
    ): float|SupportCollection {
        $query ??= static::query();

        if ($interval) {

            $grammar = $query->getQuery()->getGrammar();

            /**
             * @var SupportCollection<int, object{ date_group: string, aggregated_value: float|int }> $results
             */
            $results = $query
                ->toBase()
                ->selectRaw("{$interval->toSqlFormat($grammar::class, 'date')} as date_group")
                ->groupBy('date_group')
                ->addSelect($aggregate->toSqlSelect('number_value', 'aggregated_value'))
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
                columns: ['number_value']
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
            $interval instanceof KpiInterval ? "1 {$interval->value}" : $interval
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
