<?php

namespace Elegantly\Kpi\Enums;

use Carbon\Carbon;

enum KpiInterval: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Month = 'month';
    case Year = 'year';

    public function toDateFormat(): string
    {
        return match ($this) {
            self::Hour => 'Y-m-d H',
            self::Day => 'Y-m-d',
            self::Month => 'Y-m',
            self::Year => 'Y',
        };
    }

    public function toStartOf(Carbon $date): Carbon
    {
        return match ($this) {
            self::Hour => $date->startOfHour(),
            self::Day => $date->startOfDay(),
            self::Month => $date->startOfMonth(),
            self::Year => $date->startOfYear(),
        };
    }

    public function toEndOf(Carbon $date): Carbon
    {
        return match ($this) {
            self::Hour => $date->endOfHour(),
            self::Day => $date->endOfDay(),
            self::Month => $date->endOfMonth(),
            self::Year => $date->endOfYear(),
        };
    }
}
