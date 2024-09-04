<?php

namespace Elegantly\Kpi\Models;

use Brick\Money\Money;
use Carbon\Carbon;
use Elegantly\Kpi\Database\Factories\KpiFactory;
use Elegantly\Kpi\Enums\KpiInterval;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * @template TValue of null|float|string|Money|array<array-key, mixed>
 *
 * @property string $name
 * @property string $type
 * @property TValue $value
 * @property ?string $string_value
 * @property ?float $number_value
 * @property ?Money $money_value
 * @property ?string $money_currency
 * @property ?array<array-key, mixed> $json_value
 * @property ?string $description
 * @property ?array<int, scalar> $tags
 * @property ?array<array-key, mixed> $metadata
 * @property Carbon $date
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
        'money_value' => MoneyCast::class.':money_currency',
        'json_value' => 'array',
        'metadata' => 'array',
        'tags' => 'array',
        'date' => 'datetime',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => 'number_value',
    ];

    /**
     * @return Attribute<TValue,null|int|float|string|Money|array<array-key, mixed>>
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getValue(),
            set: function (null|int|float|string|Money|array $value) {
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
     * @return TValue
     */
    public function getValue(): null|float|string|Money|array
    {
        /**
         * @var TValue
         */
        return match ($this->type) {
            'number_value' => $this->number_value,
            'string_value' => $this->string_value,
            'money_value' => $this->money_value,
            'json_value' => $this->json_value,
            default => null,
        };
    }

    /**
     * @param  null|int|float|string|Money|array<array-key, mixed>  $value
     */
    public function setValue(
        null|int|float|string|Money|array $value
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

    public function setDescription(?string $value): static
    {
        $this->description = $value;

        return $this;
    }

    public function setDate(Carbon $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param  null|array<array-key, mixed>  $value
     */
    public function setMetadata(?array $value): static
    {
        $this->metadata = $value;

        return $this;
    }

    /**
     * @param  null|array<int, scalar>  $value
     */
    public function setTags(?array $value): static
    {
        $this->tags = $value;

        return $this;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLatestPerInterval(Builder $query, KpiInterval $interval): Builder
    {
        $grammar = $query->getQuery()->getGrammar();

        return $query->join(
            DB::raw(
                "(SELECT MAX(date) AS max_date FROM kpis GROUP BY {$interval->toSqlFormat($grammar::class, 'date')}) as subquery"
            ),
            'kpis.date',
            '=',
            'subquery.max_date'
        );
    }
}
