<?php

namespace Elegantly\Kpi;

use Elegantly\Kpi\Models\Kpi;

/**
 * @extends KpiDefinition<null|float>
 */
abstract class KpiFloatDefinition extends KpiDefinition
{
    public static function diff(?Kpi $old, ?Kpi $new): ?float
    {
        if ($old?->value === null || $new?->value === null) {
            return null;
        }

        return $new->value - $old->value;
    }
}
