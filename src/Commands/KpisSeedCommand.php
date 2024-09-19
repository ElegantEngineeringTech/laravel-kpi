<?php

namespace Elegantly\Kpi\Commands;

use Carbon\Carbon;
use Elegantly\Kpi\Traits\DiscoverKpiDefinitions;
use Illuminate\Console\Command;
use Laravel\Prompts\Progress;

class KpisSeedCommand extends Command
{
    use DiscoverKpiDefinitions;

    public $signature = 'kpis:seed {start} {end} {--only=*}';

    public $description = 'Seed your KPI between two date';

    public function getStartDate(): Carbon
    {
        /**
         * @var string $start
         */
        $start = $this->argument('start');

        return Carbon::parse($start);
    }

    public function getEndDate(): Carbon
    {
        /**
         * @var string $end
         */
        $end = $this->argument('end');

        return Carbon::parse($end);
    }

    public function handle(): int
    {
        $start = $this->getStartDate();
        $end = $this->getEndDate();

        /**
         * @var null|string[] $only
         */
        $only = $this->option('only');

        $definitions = $this->getDefinitions();

        if ($only) {
            $definitions = $definitions->filter(function ($className) use ($only) {
                return in_array($className, $only) || in_array($className::getName(), $only);
            });
        }

        $progress = new Progress(
            label: 'Snapshotting...',
            steps: $definitions->count(),
        );

        foreach ($definitions as $className) {
            $progress->hint($className);

            $interval = $className::getSnapshotInterval();

            $className::seed(
                start: $interval->toStartOf($start->clone()),
                end: $interval->toEndOf($end->clone()),
                interval: $interval,
            );

            $progress->advance();
        }

        $progress->finish();

        return self::SUCCESS;
    }
}
