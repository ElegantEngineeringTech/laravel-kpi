<?php

namespace Elegantly\Kpi\Enums;

use Illuminate\Support\Facades\DB;

enum KpiAggregate: string
{
    case Sum = 'sum';
    case Average = 'average';
    case Count = 'count';

    public function toSqlSelect(string $column, string $alias = 'aggregated_value'): \Illuminate\Contracts\Database\Query\Expression
    {
        return match ($this) {
            self::Count => DB::raw("COUNTM({$column}) as {$alias}"),
            self::Sum => DB::raw("SUM({$column}) as {$alias}"),
            self::Average => DB::raw("AVG({$column}) as {$alias}"),
        };
    }

    public function toBuilderFunction(): string
    {
        return match ($this) {
            self::Count => 'count',
            self::Sum => 'sum',
            self::Average => 'avg',
        };
    }
}
