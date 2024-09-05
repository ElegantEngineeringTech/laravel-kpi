<?php

namespace Elegantly\Kpi;

use Elegantly\Kpi\Models\Kpi;

/**
 * @template TValue of array<array-key, mixed>
 *
 * @extends KpiDefinition<null|TValue>
 */
abstract class KpiJsonDefinition extends KpiDefinition
{
    abstract public static function diff(?Kpi $old, ?Kpi $new): ?array;
}
