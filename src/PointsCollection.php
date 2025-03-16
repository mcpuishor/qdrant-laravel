<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\Point;

class PointsCollection extends Collection
{
    public function toArray():array
    {
        return $this->map(function ($item) {
            if ($item instanceof Point) {
                return $item->toArray();
            }

            if ($item instanceof Collection) {
                return (new static($item))->toArray();
            }

            if (is_array($item)) {
                return array_map(function ($value) {
                    if ($value instanceof Point) {
                        return $value->toArray();
                    }
                    if ($value instanceof Collection) {
                        return (new static($value))->toArray();
                    }
                    return $value;
                }, $item);
            }

            return $item;
        })->all();
    }

    public static function make($items = []): self
    {
        return new static($items);
    }

}
