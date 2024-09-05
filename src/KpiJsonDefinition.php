<?php

namespace Elegantly\Kpi;

use Elegantly\Kpi\Models\Kpi;

/**
 * @extends KpiDefinition<null|array<array-key, mixed>>
 */
abstract class KpiJsonDefinition extends KpiDefinition
{
    abstract public static function diff(?Kpi $old, ?Kpi $new): ?array;
}
