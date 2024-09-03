<?php

namespace Elegantly\Kpi\Commands;

use Carbon\Carbon;
use Elegantly\Kpi\Traits\DiscoverKpiDefinitions;
use Illuminate\Console\Command;
use Laravel\Prompts\Progress;

class KpisSeedCommand extends Command
{
    use DiscoverKpiDefinitions;

    public $signature = 'kpis:seed {from} {to} {--only=*}';

    public $description = 'Seed your KPI between two date';

    public function getFromDate(): Carbon
    {
        /**
         * @var string $from
         */
        $from = $this->argument('from');

        return Carbon::parse($from);
    }

    public function getToDate(): Carbon
    {
        /**
         * @var string $to
         */
        $to = $this->argument('to');

        return Carbon::parse($to);
    }

    public function handle(): int
    {
        $from = $this->getFromDate();
        $to = $this->getToDate();

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
                from: $interval->toStartOf($from->clone()),
                to: $interval->toEndOf($to->clone()),
                interval: $interval,
            );

            $progress->advance();
        }

        $progress->finish();

        return self::SUCCESS;
    }
}
