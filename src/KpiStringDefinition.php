<?php

namespace Elegantly\Kpi;

use Elegantly\Kpi\Models\Kpi;

/**
 * @extends KpiDefinition<null|string>
 */
abstract class KpiStringDefinition extends KpiDefinition
{
    abstract public static function diff(?Kpi $old, ?Kpi $new): ?string;
}
