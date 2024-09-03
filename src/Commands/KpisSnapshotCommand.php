<?php

namespace Elegantly\Kpi\Commands;

use Carbon\Carbon;
use Elegantly\Kpi\KpiDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\StructureDiscoverer\Data\DiscoveredClass;
use Spatie\StructureDiscoverer\Discover;

use function Laravel\Prompts\progress;

class KpisSnapshotCommand extends Command
{
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

    /**
     * @return Collection<int, class-string<KpiDefinition>>
     */
    public function getDiscoveredDefinitions(): Collection
    {
        /**
         * @var Collection<int, class-string<KpiDefinition>>
         */
        $definitions = new Collection;

        $discovered = Discover::in(app_path('kpis.discover.path'))
            ->classes()
            ->extending(KpiDefinition::class)
            ->get();

        foreach ($discovered as $item) {
            if ($item instanceof DiscoveredClass) {
                /**
                 * @var class-string<KpiDefinition> $className
                 */
                $className = "{$item->namespace}\{$item->name}";
                $definitions->push($className);
            }
        }

        return $definitions;
    }

    /**
     * @return Collection<int, class-string<KpiDefinition>>
     */
    public function getDefinitions(): Collection
    {
        /**
         * @var class-string<KpiDefinition>[] $registered
         */
        $registered = config('kpi.definitions') ?? [];

        if (config('kpi.discover.enabled')) {
            return $this->getDiscoveredDefinitions()
                ->push(...$registered)
                ->unique();
        }

        return collect($registered);
    }
}
