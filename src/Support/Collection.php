<?php

namespace TouristeSIM\Sdk\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * Collection class for handling model collections
 */
class Collection implements IteratorAggregate, Countable, JsonSerializable
{
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    /**
     * Create collection from array of raw data
     */
    public static function make(array $items, string $modelClass): self
    {
        $models = array_map(
            fn($item) => new $modelClass($item),
            $items
        );
        return new self($models);
    }

    /**
     * Get all items
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get item by index
     */
    public function get(int $index = null)
    {
        if ($index === null) {
            return $this->items;
        }

        return $this->items[$index] ?? null;
    }

    /**
     * Get first item
     */
    public function first()
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get last item
     */
    public function last()
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * Filter collection
     */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->items, $callback));
    }

    /**
     * Map over collection
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /**
     * Pluck specific attribute from all items
     */
    public function pluck(string $attribute): array
    {
        return array_map(
            fn($item) => $item->{$attribute} ?? null,
            $this->items
        );
    }

    /**
     * Sort collection
     */
    public function sort(callable $callback = null): self
    {
        $items = $this->items;

        if ($callback) {
            usort($items, $callback);
        } else {
            sort($items);
        }

        return new self($items);
    }

    /**
     * Sort by attribute
     */
    public function sortBy(string $attribute, bool $descending = false): self
    {
        $items = $this->items;

        usort($items, function ($a, $b) use ($attribute, $descending) {
            $aVal = $a->{$attribute} ?? '';
            $bVal = $b->{$attribute} ?? '';

            $comparison = $aVal <=> $bVal;
            return $descending ? -$comparison : $comparison;
        });

        return new self($items);
    }

    /**
     * Add item to collection
     */
    public function push($item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Count items
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Get iterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_map(
            fn($item) => method_exists($item, 'toArray') ? $item->toArray() : $item,
            $this->items
        );
    }

    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Array access
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }
}
