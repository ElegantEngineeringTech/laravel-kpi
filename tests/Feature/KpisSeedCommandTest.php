<?php

use Elegantly\Kpi\Commands\KpisSeedCommand;
use Elegantly\Kpi\Tests\TestKpiDefinition;
use Illuminate\Support\Facades\Artisan;

it('can seed kpis between the right dates using the command', function () {

    config()->set('kpi.definitions', [
        TestKpiDefinition::class,
    ]);

    $hours = 23 * 2;
    $from = now()->subHours($hours);
    $to = now();

    Artisan::call(KpisSeedCommand::class, [
        'from' => $from,
        'to' => $to,
    ]);

    expect(
        TestKpiDefinition::count()
    )->toBe(
        $hours + 1
    );
});
