<?php

namespace Elegantly\Kpi;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * @implements Arrayable<string, mixed>
 */
class KpiAggregatedValue implements Arrayable, Jsonable
{
    public function __construct(
        public Carbon $date,
        public int|float|null $value,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'value' => $this->value,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options) ?: '';
    }
}
