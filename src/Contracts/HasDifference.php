<?php

namespace Elegantly\Kpi\Contracts;

use Brick\Money\Money;
use Elegantly\Kpi\Models\Kpi;

/**
 * @template TValue of null|float|string|Money|array<array-key, mixed>
 */
interface HasDifference
{
    /**
     * Get the difference between two KPI values
     *
     * @param  Kpi<TValue>  $old
     * @param  Kpi<TValue>  $new
     * @return TValue
     */
    public static function diff(?Kpi $old, ?Kpi $new): null|float|string|Money|array;
}
