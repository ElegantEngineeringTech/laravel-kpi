<?php

namespace Elegantly\Kpi;

use Carbon\Carbon;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\Models\Kpi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\ArrayObject;

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

    abstract public static function getInterval(): KpiInterval;

    /**
     * @return Builder<Kpi>
     */
    public static function query(
        ?Carbon $from = null,
        ?Carbon $to = null
    ): Builder {
        $interval = static::getInterval();

        $query = Kpi::query()->where('name', static::getName());

        if ($from) {
            $query->where('created_at', '>=', $interval->toStartOf($from));
        }
        if ($to) {
            $query->where('created_at', '<=', $interval->toEndOf($to));
        }

        return $query;
    }

    public static function create(Carbon $date): Kpi
    {
        $definition = new static($date);

        $kpi = new Kpi;

        $kpi
            ->setName(static::getName())
            ->setValue($definition->getValue())
            ->setMetadata($definition->getMetadata())
            ->setDescription($definition->getDescription())
            ->setTags($definition->getTags())
            ->setCreatedAt($date->clone())
            ->save();

        return $kpi;
    }
}
