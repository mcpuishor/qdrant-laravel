<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Vectors
{
    use HasFilters;
    public function __construct(
        private readonly QdrantTransport $transport,
        private readonly string          $collection,
    ){}

    public function update(PointsCollection $collection): bool
    {
        return $this->transport->request(
            method: 'PUT',
            uri: "/collections/{$this->collection}/points/vectors",
            options: [
                'json' => [
                    "points" => $collection->toArray(),
                ]
            ]
        )->isOk();
    }

    public function delete(array $ids): bool
    {
        return $this->transport->request(
            method: 'POST',
            uri: "/collections/{$this->collection}/points/vectors/delete",
            options: [
                'json' => [
                    'points' => $ids,
                ]
            ]
        )->isOk();
    }
}
