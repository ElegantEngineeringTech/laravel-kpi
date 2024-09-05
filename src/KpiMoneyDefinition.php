<?php

namespace Elegantly\Kpi;

use Brick\Money\Money;
use Elegantly\Kpi\Models\Kpi;

/**
 * @extends KpiDefinition<null|Money>
 */
abstract class KpiMoneyDefinition extends KpiDefinition
{
    public static function diff(?Kpi $old, ?Kpi $new): ?Money
    {
        if ($old?->value === null || $new?->value === null) {
            return null;
        }

        return $new->value->minus($new->value);
    }
}
