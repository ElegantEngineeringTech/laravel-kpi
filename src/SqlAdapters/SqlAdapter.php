<?php

declare(strict_types=1);

namespace Elegantly\Kpi\SqlAdapters;

use Elegantly\Kpi\Enums\KpiInterval;

interface SqlAdapter
{
    public static function datetime(
        KpiInterval $interval,
        string $column
    ): string;
}
