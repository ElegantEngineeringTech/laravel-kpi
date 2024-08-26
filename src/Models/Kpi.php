<?php

namespace Elegantly\Kpi\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property string $type
 * @property null|string|int|float|ArrayObject<int|string, mixed> $value
 * @property ?string $string_value
 * @property ?float $number_value
 * @property ?int $money_value
 * @property ?string $money_currency
 * @property ?ArrayObject<int|string, mixed> $json_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Kpi extends Model
{
    protected $guarded = [];

    protected $casts = [
        'number_value' => 'float',
        'json_value' => 'array',
        'metadata' => AsArrayObject::class,
    ];

    /**
     * @return Attribute<null|string|int|float|ArrayObject<int|string, mixed>,  never>
     */
    public function value(): Attribute
    {
        return Attribute::get(fn () => match ($this->type) {
            'number_value' => $this->number_value,
            'json_value' => $this->json_value,
            'string_value' => $this->string_value,
            'money_value' => $this->money_value,
            default => null,
        });
    }
}
