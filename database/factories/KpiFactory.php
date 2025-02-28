<?php

declare(strict_types=1);

namespace Elegantly\Kpi\Database\Factories;

use Elegantly\Kpi\Models\Kpi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kpi>
 */
class KpiFactory extends Factory
{
    protected $model = Kpi::class;

    public function definition()
    {
        return [];
    }
}
