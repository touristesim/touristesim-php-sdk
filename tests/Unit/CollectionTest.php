<?php

namespace TouristeSIM\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TouristeSIM\Sdk\Support\Collection;

class CollectionTest extends TestCase
{
    private array $sampleData;

    protected function setUp(): void
    {
        $this->sampleData = [
            ['id' => 1, 'name' => 'Plan A', 'price' => 10],
            ['id' => 2, 'name' => 'Plan B', 'price' => 20],
            ['id' => 3, 'name' => 'Plan C', 'price' => 15],
        ];
    }

    public function test_collection_creation()
    {
        $collection = Collection::make($this->sampleData);
        
        $this->assertEquals(3, count($collection));
    }

    public function test_collection_get()
    {
        $collection = Collection::make($this->sampleData);
        
        $this->assertEquals('Plan A', $collection->get(0)['name']);
    }

    public function test_collection_first()
    {
        $collection = Collection::make($this->sampleData);
        
        $this->assertEquals('Plan A', $collection->first()['name']);
    }

    public function test_collection_last()
    {
        $collection = Collection::make($this->sampleData);
        
        $this->assertEquals('Plan C', $collection->last()['name']);
    }

    public function test_collection_filter()
    {
        $collection = Collection::make($this->sampleData);
        
        $filtered = $collection->filter(fn($item) => $item['price'] > 12);
        
        $this->assertEquals(2, count($filtered)); // Plan B and Plan C
    }

    public function test_collection_map()
    {
        $collection = Collection::make($this->sampleData);
        
        $names = $collection->map(fn($item) => $item['name']);
        
        $this->assertEquals('Plan A', $names[0]);
        $this->assertEquals('Plan B', $names[1]);
    }

    public function test_collection_pluck()
    {
        $collection = Collection::make($this->sampleData);
        
        $prices = $collection->pluck('price');
        
        $this->assertEquals(3, count($prices));
        $this->assertContains(10, $prices);
        $this->assertContains(20, $prices);
    }

    public function test_collection_sort_by()
    {
        $collection = Collection::make($this->sampleData);
        
        $sorted = $collection->sortBy('price');
        
        $this->assertEquals(10, $sorted[0]['price']);
        $this->assertEquals(15, $sorted[1]['price']);
        $this->assertEquals(20, $sorted[2]['price']);
    }

    public function test_collection_sort_by_descending()
    {
        $collection = Collection::make($this->sampleData);
        
        $sorted = $collection->sortBy('price', true);
        
        $this->assertEquals(20, $sorted[0]['price']);
        $this->assertEquals(15, $sorted[1]['price']);
        $this->assertEquals(10, $sorted[2]['price']);
    }

    public function test_collection_is_empty()
    {
        $collection = Collection::make([]);
        
        $this->assertTrue($collection->isEmpty());
    }

    public function test_collection_is_not_empty()
    {
        $collection = Collection::make($this->sampleData);
        
        $this->assertFalse($collection->isEmpty());
    }

    public function test_collection_to_array()
    {
        $collection = Collection::make($this->sampleData);
        
        $array = $collection->toArray();
        
        $this->assertEquals($this->sampleData, $array);
    }

    public function test_collection_iteration()
    {
        $collection = Collection::make($this->sampleData);
        
        $count = 0;
        foreach ($collection as $item) {
            $count++;
        }
        
        $this->assertEquals(3, $count);
    }
}
