<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Vectors
{
    use HasFilters;
    public function __construct(
        private QdrantTransport $transport,
        private string          $collection,
    ){
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points/vectors");
    }

    public function update(PointsCollection $collection): bool
    {
        return $this->transport->put(
            uri: "",
            options: [
                'json' => [
                    "points" => $collection->toArray(),
                ]
            ]
        )->isOk();
    }

    public function delete(array $ids): bool
    {
        return $this->transport->post(
            uri: "/delete",
            options: [
                'json' => [
                    'points' => $ids,
                ]
            ]
        )->isOk();
    }
}
