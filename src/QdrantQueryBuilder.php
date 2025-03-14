<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Mcpuishor\QdrantLaravel\QdrantClient;

class QdrantQueryBuilder
{
    use Macroable;

    protected QdrantClient $client;
    protected string $collection;
    protected array $filters = [];
    protected ?int $limit = null;
    protected array $sort = [];
    protected ?array $vector = null;

    public function __construct(QdrantClient $client, string $collection)
    {
        $this->client = $client;
        $this->collection = $collection;
    }

    public function getPoints(int|string|array $ids, bool $withPayload = true, bool $withVector=false): array
    {
       if (is_array($ids)) {
           return $this->getPointsById($ids, $withPayload, $withVector);
       }

       return $this->find($ids);
    }

    public function getPointsById(array $ids, bool $withPayload = true, bool $withVector=false): array
    {
        return $this->client
            ->request(
                method: 'POST',
                uri: "/collections/{$this->collection}/points",
                options: [
                    'json' => [
                        'ids' => $ids,
                        'with_payload' => $withPayload,
                        'with_vector' => $withVector,
                    ],
                ]
            );
    }

    public function find(int $id): array
    {
        return $this->client
            ->request(
                method: 'GET',
                uri: "/collections/{$this->collection}/points/{$id}"
            );
    }

    public function delete(): bool
    {

    }
}
