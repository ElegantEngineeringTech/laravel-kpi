<?php

namespace Elegantly\Kpi;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * @template TValue
 *
 * @implements Arrayable<string, TValue>
 */
class KpiValue implements Arrayable, Jsonable
{
    /**
     * @param  TValue  $value
     */
    public function __construct(
        public Carbon $date,
        public mixed $value,
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
