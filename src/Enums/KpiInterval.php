<?php

namespace Elegantly\Kpi\Enums;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Elegantly\Kpi\SqlAdapters\MySqlAdapter;
use Elegantly\Kpi\SqlAdapters\PostgreSqlAdapter;
use Elegantly\Kpi\SqlAdapters\SqliteAdapter;
use Error;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;

enum KpiInterval: string
{
    case Minute = 'minute';
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
            self::Minute => 'second',
            self::Hour => self::Minute->toUnit(),
            self::Day => self::Hour->toUnit(),
            self::Month => self::Day->toUnit(),
            self::Year => self::Month->toUnit(),
        };
    }

    public function toDateFormat(): string
    {
        return match ($this) {
            self::Minute => 'Y-m-d H:i:00',
            self::Hour => 'Y-m-d H:00',
            self::Day => 'Y-m-d',
            self::Month => 'Y-m',
            self::Year => 'Y',
        };
    }

    public function fromDateFormat(string $date): ?Carbon
    {
        return Carbon::createFromFormat($this->toDateFormat(), $date);
    }

    public function toSqlFormat(string $driver, string $column): string
    {
        return match ($driver) {
            MySqlGrammar::class, 'mysql', 'mariadb' => MySqlAdapter::datetime($this, $column),
            SQLiteGrammar::class, 'sqlite' => SqliteAdapter::datetime($this, $column),
            PostgresGrammar::class, 'pgsql' => PostgreSqlAdapter::datetime($this, $column),
            default => throw new Error('Unsupported database driver.'),
        };
    }

    public function toStartOf(?Carbon $date = null): Carbon
    {
        $date ??= now();

        return $date->startOf($this->toUnit());
    }

    public function toEndOf(?Carbon $date = null): Carbon
    {
        $date ??= now();

        return $date->endOf($this->toUnit());
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