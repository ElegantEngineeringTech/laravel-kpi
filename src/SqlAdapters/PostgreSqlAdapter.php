<?php

declare(strict_types=1);

namespace Elegantly\Kpi\SqlAdapters;

use Elegantly\Kpi\Enums\KpiInterval;

class PostgreSqlAdapter implements SqlAdapter
{
    public static function datetime(
        KpiInterval $interval,
        string $column
    ): string {
        return match ($interval) {
            KpiInterval::Minute => "TO_CHAR({$column}, 'YYYY-MM-DD HH24:MI:00')",
            KpiInterval::Hour => "TO_CHAR({$column}, 'YYYY-MM-DD HH24:00')",
            KpiInterval::Day => "TO_CHAR({$column}, 'YYYY-MM-DD')",
            KpiInterval::Month => "TO_CHAR({$column}, 'YYYY-MM')",
            KpiInterval::Year => "TO_CHAR({$column}, 'YYYY')",
        };
    }
}
