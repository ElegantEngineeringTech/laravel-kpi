<?php

namespace Elegantly\Kpi\Models;

use Brick\Money\Money;
use Carbon\Carbon;
use Elegantly\Kpi\Database\Factories\KpiFactory;
use Elegantly\Money\MoneyCast;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property string $name
 * @property string $type
 * @property null|string|float|ArrayObject<int|string, mixed> $value
 * @property ?string $string_value
 * @property ?float $number_value
 * @property ?Money $money_value
 * @property ?string $money_currency
 * @property-read ?ArrayObject<int|string, mixed> $json_value
 * @property-write null|array<int|string, mixed>|ArrayObject<int|string, mixed> $json_value
 * @property ?string $description
 * @property null|array<int, int|string> $tags
 * @property ?ArrayObject<int|string, mixed> $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Kpi extends Model
{
    /**
     * @use HasFactory<KpiFactory>
     */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'number_value' => 'float',
        'json_value' => AsArrayObject::class,
        'money_value' => MoneyCast::class.':money_currency',
        'metadata' => AsArrayObject::class,
        'tags' => 'array',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => 'number_value',
    ];

    /**
     * @return Attribute<null|string|int|float|ArrayObject<int|string, mixed>,null|string|int|float|ArrayObject<int|string, mixed>>
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getValue(),
            set: function (null|int|float|string|array|Money|Arrayable $value) {
                $this->setValue($value);

                return Arr::only($this->attributes, [
                    'type',
                    'number_value',
                    'json_value',
                    'string_value',
                    'money_value',
                    'money_currency',
                ]);
            }
        );
    }

    /**
     * @return null|string|int|float|ArrayObject<int|string, mixed>
     */
    public function getValue(): null|string|int|float|ArrayObject
    {
        return match ($this->type) {
            'number_value' => $this->number_value,
            'json_value' => $this->json_value,
            'string_value' => $this->string_value,
            'money_value' => $this->money_value,
            default => null,
        };
    }

    /**
     * @param  null|int|float|string|mixed[]|Arrayable<int|string, mixed>|Money  $value
     */
    public function setValue(
        null|int|float|string|array|Arrayable|Money $value
    ): static {
        $this->resetValue();

        if (is_int($value) || is_float($value)) {
            $this->type = 'number_value';
            $this->number_value = (float) $value;
        } elseif (is_string($value)) {
            $this->type = 'string_value';
            $this->string_value = $value;
        } elseif (is_array($value)) {
            $this->type = 'json_value';
            $this->json_value = $value;
        } elseif ($value instanceof Arrayable) {
            $this->type = 'json_value';
            $this->json_value = $value->toArray();
        } elseif ($value instanceof Money) {
            $this->type = 'money_value';
            $this->money_value = $value;
        }

        return $this;
    }

    public function resetValue(): static
    {
        $this->type = 'number_value';
        $this->number_value = null;
        $this->json_value = null;
        $this->string_value = null;
        $this->money_value = null;

        return $this;
    }

    public function setName(string $value): static
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @param  null|array<int|string, mixed>|ArrayObject<int|string, mixed>  $value
     */
    public function setMetadata(array|ArrayObject|null $value): static
    {
        if (is_array($value)) {
            $this->metadata = new ArrayObject($value);
        } else {
            $this->metadata = $value;
        }

        return $this;
    }

    public function setDescription(?string $value): static
    {
        $this->description = $value;

        return $this;
    }

    /**
     * @param  null|array<int, int|string>|Arrayable<int, int|string>  $value
     */
    public function setTags(null|array|Arrayable $value): static
    {
        if ($value instanceof Arrayable) {
            /** @var array<int, int|string> $array */
            $array = $value->toArray();
            $this->tags = $array;
        } else {
            $this->tags = $value;
        }

        return $this;
    }
}
