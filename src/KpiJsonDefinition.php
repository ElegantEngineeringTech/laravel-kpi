<?php

declare(strict_types=1);

namespace Elegantly\Kpi;

use Elegantly\Kpi\Models\Kpi;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends KpiDefinition<null|array<TKey, TValue>>
 */
abstract class KpiJsonDefinition extends KpiDefinition
{
    abstract public static function diff(?Kpi $old, ?Kpi $new): ?array;
}
