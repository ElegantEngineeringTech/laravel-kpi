<?php

namespace Elegantly\Kpi\Database\Factories;

use Elegantly\Kpi\Contracts\KpiModelInterface;
use Elegantly\Kpi\Models\Kpi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Kpi>
 */
class KpiFactory extends Factory
{
    public function __construct(
        $count = null,
        ?Collection $states = null,
        ?Collection $has = null,
        ?Collection $for = null,
        ?Collection $afterMaking = null,
        ?Collection $afterCreating = null,
        $connection = null,
        ?Collection $recycle = null,
        bool $expandRelationships = true,
    ) {
        $this->model = app()->make(KpiModelInterface::class);
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection, $recycle, $expandRelationships);
    }

    public function definition()
    {
        return [];
    }
}
