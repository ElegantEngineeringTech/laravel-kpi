<?php

namespace Elegantly\Kpi\SqlAdapters;

use Elegantly\Kpi\Enums\KpiInterval;

class MySqlAdapter implements SqlAdapter
{
    public static function datetime(
        KpiInterval $interval,
        string $column
    ): string {
        return match ($interval) {
            KpiInterval::Minute => "date_format({$column}, '%Y-%m-%d %H:%i:00')",
            KpiInterval::Hour => "date_format({$column}, '%Y-%m-%d %H:00')",
            KpiInterval::Day => "date_format({$column}, '%Y-%m-%d')",
            KpiInterval::Month => "date_format({$column}, '%Y-%m')",
            KpiInterval::Year => "date_format({$column}, '%Y')",
        };
    }
}
