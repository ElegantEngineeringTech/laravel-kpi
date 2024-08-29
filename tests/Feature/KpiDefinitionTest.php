<?php

use Elegantly\Kpi\Tests\TestKpiDefinition;

it('query the kpis between the right dates', function () {

    foreach (range(0, 3) as $i) {
        TestKpiDefinition::create(now()->subHours($i));
    }

    $query = TestKpiDefinition::query(
        from: now()->subHours(1),
        to: now()
    );

    expect($query->count())->toBe(2);
});
