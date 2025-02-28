<?php

declare(strict_types=1);

namespace Elegantly\Kpi\Enums;

use Illuminate\Support\Facades\DB;

enum KpiAggregate: string
{
    case Max = 'max';
    case Min = 'min';
    case Sum = 'sum';
    case Average = 'average';
    case Count = 'count';

    public function toSqlSelect(string $column, string $alias = 'aggregated_value'): \Illuminate\Contracts\Database\Query\Expression
    {
        return match ($this) {
            self::Max => DB::raw("MAX({$column}) as {$alias}"),
            self::Min => DB::raw("MIN({$column}) as {$alias}"),
            self::Count => DB::raw("COUNT({$column}) as {$alias}"),
            self::Sum => DB::raw("SUM({$column}) as {$alias}"),
            self::Average => DB::raw("AVG({$column}) as {$alias}"),
        };
    }

    public function toBuilderFunction(): string
    {
        return match ($this) {
            self::Max => 'max',
            self::Min => 'min',
            self::Count => 'count',
            self::Sum => 'sum',
            self::Average => 'avg',
        };
    }
}
