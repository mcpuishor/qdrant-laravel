<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\PointsCollection;

class Autochunk {
    private PointsCollection $collection;
    public function __construct(
        private Points $points,
        private int $chunk_size,
    ){
        $this->collection = new PointsCollection();
    }

    public function add(Point $point)
    {
        if ($this->collection->count() >= $this->chunk_size) {
            $this->flush();
        }

        $this->collection->push($point);
    }

    public function count():int
    {
        return $this->collection->count();
    }

    public function flush(): bool
    {
        if( $this->collection->count() > 0 ) {
            $this->points->upsert($this->collection);
            $this->collection = $this->collection->empty();
        }

        return true;
    }

    public function __destruct()
    {
        if ($this->collection->count() > 0) {
            $this->flush();
        }
    }
}
