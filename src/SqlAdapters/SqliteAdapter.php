<?php

declare(strict_types=1);

namespace Elegantly\Kpi\SqlAdapters;

use Elegantly\Kpi\Enums\KpiInterval;

class SqliteAdapter implements SqlAdapter
{
    public static function datetime(
        KpiInterval $interval,
        string $column
    ): string {
        return match ($interval) {
            KpiInterval::Minute => "strftime('%Y-%m-%d %H:%M:00', {$column})",
            KpiInterval::Hour => "strftime('%Y-%m-%d %H:00', {$column})",
            KpiInterval::Day => "strftime('%Y-%m-%d', {$column})",
            KpiInterval::Month => "strftime('%Y-%m', {$column})",
            KpiInterval::Year => "strftime('%Y', {$column})",
        };
    }
}
