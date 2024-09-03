<?php

namespace Elegantly\Kpi\Tests;

use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Kpi\KpiDefinition;

class TestKpiDefinition extends KpiDefinition
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
}
