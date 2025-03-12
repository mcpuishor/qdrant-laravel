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

    public function where(string $field, string $operator, mixed $value): static
    {
        $this->filters[] = compact('field', 'operator', 'value');
        return $this;
    }

    public function limit(int $value): static
    {
        $this->limit = $value;
        return $this;
    }

    public function orderBy(string $field, string $direction = 'asc'): static
    {
        $this->sort[] = compact('field', 'direction');
        return $this;
    }

    public function searchVector(array $vector): static
    {
        $this->vector = $vector;
        return $this;
    }

    public function get(): Collection
    {
        $query = [
            'filter' => $this->filters,
            'limit'  => $this->limit,
            'sort'   => $this->sort,
        ];

        if ($this->vector) {
            $query['vector'] = $this->vector;
        }

        $response = $this->client->request('POST', "/collections/{$this->collection}/points/search", [
            'json' => $query
        ]);

        return collect($response['result'] ?? []);
    }

    public function count(): int
    {
        $query = ['filter' => $this->filters];

        $response = $this->client->request('POST', "/collections/{$this->collection}/points/count", [
            'json' => $query
        ]);

        return $response['result']['count'] ?? 0;
    }

    public function delete(): bool
    {
        $query = ['filter' => $this->filters];

        $response = $this->client->request('POST', "/collections/{$this->collection}/points/delete", [
            'json' => $query
        ]);

        return isset($response['status']) && $response['status'] === 'ok';
    }
}
