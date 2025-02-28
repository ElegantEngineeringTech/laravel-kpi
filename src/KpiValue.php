<?php

declare(strict_types=1);

namespace Elegantly\Kpi;

use Carbon\CarbonInterface;
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
        public CarbonInterface $date,
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
