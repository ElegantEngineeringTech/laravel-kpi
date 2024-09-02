<?php

namespace Elegantly\Kpi\Enums;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

enum KpiInterval: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Month = 'month';
    case Year = 'year';

    public function toUnit(): string
    {
        return $this->value;
    }

    public function toSmallerUnit(): string
    {
        return match ($this) {
            self::Hour => 'minute',
            self::Day => self::Hour->toUnit(),
            self::Month => self::Day->toUnit(),
            self::Year => self::Month->toUnit(),
        };
    }

    public function toDateFormat(): string
    {
        return match ($this) {
            self::Hour => 'Y-m-d H',
            self::Day => 'Y-m-d',
            self::Month => 'Y-m',
            self::Year => 'Y',
        };
    }

    public function fromDateFormat(string $date): ?Carbon
    {
        return Carbon::createFromFormat($this->toDateFormat(), $date);
    }

    public function toSqlFormat(string $column): string
    {
        return match ($this) {
            self::Hour => "strftime('%Y-%m-%d %H', {$column})",
            self::Day => "strftime('%Y-%m-%d', {$column})",
            self::Month => "strftime('%Y-%m', {$column})",
            self::Year => "strftime('%Y', {$column})",
        };
    }

    public function toStartOf(?Carbon $date = null): Carbon
    {
        $date ??= now();

        return match ($this) {
            self::Hour => $date->startOfHour(),
            self::Day => $date->startOfDay(),
            self::Month => $date->startOfMonth(),
            self::Year => $date->startOfYear(),
        };
    }

    public function toEndOf(?Carbon $date = null): Carbon
    {
        $date ??= now();

        return match ($this) {
            self::Hour => $date->endOfHour(),
            self::Day => $date->endOfDay(),
            self::Month => $date->endOfMonth(),
            self::Year => $date->endOfYear(),
        };
    }

    public function toPerdiod(
        Carbon $start,
        Carbon $end,
    ): CarbonPeriod {
        /**
         * @var CarbonPeriod
         */
        return CarbonPeriod::between(
            start: $this->toStartOf($start->clone()),
            end: $this->toEndOf($end->clone())
        )->interval("1 {$this->value}");
    }
}
