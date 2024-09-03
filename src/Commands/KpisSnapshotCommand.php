<?php

namespace Elegantly\Kpi\Commands;

use Carbon\Carbon;
use Elegantly\Kpi\Traits\DiscoverKpiDefinitions;
use Illuminate\Console\Command;

use function Laravel\Prompts\progress;

class KpisSnapshotCommand extends Command
{
    use DiscoverKpiDefinitions;

    public $signature = 'kpis:snapshot {--date=} {--interval=}';

    public $description = 'Take a snapshot of all your defined KPI';

    public function handle(): int
    {
        $interval = $this->option('interval');

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        $definitions = $this->getDefinitions();

        if ($interval) {
            $definitions = $definitions->filter(function (string $className) use ($interval) {
                return $className::getSnapshotInterval()->value === $interval;
            });
        }

        progress(
            'Snapshotting...',
            $definitions,
            function (string $class) use ($date) {
                $class::snapshot($date);
            }
        );

        return self::SUCCESS;
    }
}
