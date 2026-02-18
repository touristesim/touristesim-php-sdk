<?php

namespace TouristeSIM\Sdk\Models;

/**
 * Base Model class
 */
abstract class Model
{
    protected array $attributes = [];
    protected array $casts = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set attribute with casting
     */
    protected function setAttribute(string $key, mixed $value): void
    {
        if (isset($this->casts[$key])) {
            $value = $this->castAttribute($key, $value);
        }
        $this->attributes[$key] = $value;
    }

    /**
     * Cast attribute to proper type
     */
    protected function castAttribute(string $key, mixed $value): mixed
    {
        $castType = $this->casts[$key];

        return match ($castType) {
            'int', 'integer' => (int)$value,
            'float', 'double' => (float)$value,
            'string' => (string)$value,
            'bool', 'boolean' => (bool)$value,
            'array' => (array)$value,
            default => $value,
        };
    }

    /**
     * Get attribute
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic getter
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get all attributes
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get model as JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

/**
 * Plan model
 */
class Plan extends Model
{
    protected array $fillable = [
        'id',
        'name',
        'slug',
        'type',
        'price',
        'currency',
        'validity_days',
        'data',
        'reloadable',
        'region',
        'countries',
        'countries_count',
        'voice_minutes',
        'sms_count',
        'provider',
    ];

    protected array $casts = [
        'id' => 'int',
        'price' => 'float',
        'validity_days' => 'int',
        'reloadable' => 'bool',
        'countries_count' => 'int',
    ];

    public function getId(): int
    {
        return $this->getAttribute('id');
    }

    public function getName(): string
    {
        return $this->getAttribute('name') ?? '';
    }

    public function getSlug(): string
    {
        return $this->getAttribute('slug') ?? '';
    }

    public function getType(): string
    {
        return $this->getAttribute('type') ?? 'local';
    }

    public function isLocal(): bool
    {
        return $this->getType() === 'local';
    }

    public function isRegional(): bool
    {
        return $this->getType() === 'regional' || $this->getType() === 'region';
    }

    public function isGlobal(): bool
    {
        return $this->getType() === 'global';
    }

    public function getPrice(): float
    {
        return (float)$this->getAttribute('price');
    }

    public function getCurrency(): string
    {
        return $this->getAttribute('currency') ?? 'USD';
    }

    public function getValidityDays(): int
    {
        return $this->getAttribute('validity_days') ?? 0;
    }

    public function isReloadable(): bool
    {
        return $this->getAttribute('reloadable') ?? false;
    }

    public function getDataGB(): float
    {
        $data = $this->getAttribute('data') ?? [];
        return $data['amount'] ?? 0;
    }

    public function isUnlimited(): bool
    {
        $data = $this->getAttribute('data') ?? [];
        return $data['unlimited'] ?? false;
    }

    public function getCountriesCount(): int
    {
        return $this->getAttribute('countries_count') ?? 0;
    }

    public function getCountries(): array
    {
        return $this->getAttribute('countries') ?? [];
    }

    public function getRegion(): ?array
    {
        return $this->getAttribute('region');
    }

    public function getProvider(): string
    {
        return $this->getAttribute('provider') ?? 'Unknown';
    }
}

/**
 * Country model
 */
class Country extends Model
{
    protected array $fillable = ['code', 'name', 'slug', 'flag', 'flag_sm', 'flag_lg', 'plans_count', 'is_featured'];

    protected array $casts = [
        'plans_count' => 'int',
        'is_featured' => 'bool',
    ];

    public function getCode(): string
    {
        return $this->getAttribute('code') ?? '';
    }

    public function getName(): string
    {
        return $this->getAttribute('name') ?? '';
    }

    public function getPlansCount(): int
    {
        return $this->getAttribute('plans_count') ?? 0;
    }

    public function isFeatured(): bool
    {
        return $this->getAttribute('is_featured') ?? false;
    }

    public function getFlagUrl(): string
    {
        return $this->getAttribute('flag') ?? '';
    }
}

/**
 * Order model
 */
class Order extends Model
{
    protected array $fillable = [
        'id',
        'plan_id',
        'quantity',
        'status',
        'total_price',
        'currency',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'int',
        'plan_id' => 'int',
        'quantity' => 'int',
        'total_price' => 'float',
    ];

    public function getId(): int
    {
        return $this->getAttribute('id');
    }

    public function getStatus(): string
    {
        return $this->getAttribute('status') ?? 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->getStatus() === 'completed';
    }

    public function isPending(): bool
    {
        return $this->getStatus() === 'pending';
    }

    public function isCancelled(): bool
    {
        return $this->getStatus() === 'cancelled';
    }

    public function getTotalPrice(): float
    {
        return (float)$this->getAttribute('total_price');
    }

    public function getQuantity(): int
    {
        return $this->getAttribute('quantity') ?? 0;
    }
}

/**
 * eSIM model
 */
class Esim extends Model
{
    protected array $fillable = [
        'iccid',
        'status',
        'plan',
        'balance_data',
        'validity_end',
        'activation_date',
    ];

    public function getIccid(): string
    {
        return $this->getAttribute('iccid') ?? '';
    }

    public function getStatus(): string
    {
        return $this->getAttribute('status') ?? 'pending';
    }

    public function isActive(): bool
    {
        return $this->getStatus() === 'active';
    }

    public function isPending(): bool
    {
        return $this->getStatus() === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->getStatus() === 'expired';
    }

    public function getBalanceData(): float
    {
        return (float)($this->getAttribute('balance_data') ?? 0);
    }

    public function getValidityEnd(): ?string
    {
        return $this->getAttribute('validity_end');
    }
}
