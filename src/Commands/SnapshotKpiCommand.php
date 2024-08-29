<?php

namespace Elegantly\Kpi\Commands;

use Carbon\Carbon;
use Elegantly\Kpi\KpiDefinition;
use Illuminate\Console\Command;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

use function Laravel\Prompts\progress;

class SnapshotKpiCommand extends Command
{
    public $signature = 'kpi:snapshot {--date=}';

    public $description = 'Take a snapshot of all your defined KPI';

    public function handle(): int
    {
        $this->comment('All done');

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        $definitions = $this->getDefinitions();

        progress(
            'Snapshoting...',
            $definitions,
            function (string $class) use ($date) {
                $class::create($date);
            }
        );

        return self::SUCCESS;
    }

    /**
     * @return iterable<string>
     */
    public function getDefinitions(): iterable
    {
        if (config('kpi.discover_kpi_definitions')) {
            return array_map(
                function (DiscoveredStructure|string $item) {
                    if ($item instanceof DiscoveredStructure) {
                        return $item->name;
                    }

                    return $item;
                },
                Discover::in(app_path('kpis.discover_kpi_definitions'))
                    ->classes()
                    ->extending(KpiDefinition::class)
                    ->get()
            );
        }

        /**
         * @var null|string[] $definitions
         */
        $definitions = config('kpi.definitions');

        return $definitions ?? [];
    }
}
