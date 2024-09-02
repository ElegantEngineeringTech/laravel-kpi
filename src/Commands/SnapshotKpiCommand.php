<?php

namespace Elegantly\Kpi\Commands;

use Carbon\Carbon;
use Elegantly\Kpi\KpiDefinition;
use Illuminate\Console\Command;
use Spatie\StructureDiscoverer\Data\DiscoveredClass;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use Spatie\StructureDiscoverer\Discover;

use function Laravel\Prompts\progress;

class SnapshotKpiCommand extends Command
{
    public $signature = 'kpi:snapshot {--date=} {--interval=}';

    public $description = 'Take a snapshot of all your defined KPI';

    public function handle(): int
    {
        $interval = $this->option('interval');

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        $definitions = $this->getDefinitions();

        if ($interval) {
            $definitions = array_filter($definitions, fn (string $className) => $className::getSnapshotInterval()->value === $interval);
        }

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
     * @return class-string<KpiDefinition>[]
     */
    public function getDiscoveredDefinitions(): array
    {
        /**
         * @var class-string<KpiDefinition>[]
         */
        return collect(
            Discover::in(app_path('kpis.discover_kpi_definitions'))
                ->classes()
                ->extending(KpiDefinition::class)
                ->get()
        )
            ->flatMap(function (DiscoveredStructure|string $structure) {
                if ($structure instanceof DiscoveredClass) {
                    return [$structure->name];
                }

                return [];
            })->toArray();
    }

    /**
     * @return class-string<KpiDefinition>[]
     */
    public function getDefinitions(): array
    {
        if (config('kpi.discover_kpi_definitions')) {
            return $this->getDiscoveredDefinitions();
        }

        /**
         * @var class-string<KpiDefinition>[]
         */
        return config('kpi.definitions') ?? [];
    }
}
