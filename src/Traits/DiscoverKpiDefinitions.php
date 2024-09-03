<?php

namespace Elegantly\Kpi\Traits;

use Elegantly\Kpi\KpiDefinition;
use Illuminate\Support\Collection;
use Spatie\StructureDiscoverer\Discover;

trait DiscoverKpiDefinitions
{
    /**
     * @return Collection<int, class-string<KpiDefinition>>
     */
    public function getDiscoveredDefinitions(): Collection
    {
        /**
         * @var Collection<int, class-string<KpiDefinition>>
         */
        $definitions = new Collection;

        /**
         * @var string $path
         */
        $path = config('kpi.discover.path');

        $discovered = Discover::in(app_path($path))
            ->classes()
            ->extending(KpiDefinition::class)
            ->get();

        foreach ($discovered as $item) {
            if (is_string($item)) {
                /**
                 * @var class-string<KpiDefinition> $item
                 */
                $definitions->push($item);
            } else {
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
