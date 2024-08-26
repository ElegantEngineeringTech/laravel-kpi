<?php

namespace Elegantly\Kpi\Commands;

use Illuminate\Console\Command;

class KpiCommand extends Command
{
    public $signature = 'laravel-kpi';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
