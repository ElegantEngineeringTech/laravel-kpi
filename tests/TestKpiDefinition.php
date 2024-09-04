<?php

namespace Elegantly\Kpi\Tests;

use Elegantly\Kpi\Contracts\HasDifference;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiDefinition;
use Elegantly\Kpi\Models\Kpi;

/**
 * @extends KpiDefinition<null|float>
 *
 * @implements HasDifference<null|float>
 */
class TestKpiDefinition extends KpiDefinition implements HasDifference
{
    public static function getName(): string
    {
        return 'test';
    }

    public static function getSnapshotInterval(): KpiInterval
    {
        return KpiInterval::Hour;
    }

    public function getValue(): ?float
    {
        return rand(100, 200) / 10;
    }

    public function getTags(): ?array
    {
        return ['foo', 'bar'];
    }

    public function getDescription(): ?string
    {
        return 'description';
    }

    public function getMetadata(): ?array
    {
        return [
            'user' => 1,
        ];
    }

    public static function diff(?Kpi $old, ?Kpi $new): ?float
    {
        if ($new?->value === null || $old?->value === null) {
            return null;
        }

        return $new->value - $old->value;
    }
}
