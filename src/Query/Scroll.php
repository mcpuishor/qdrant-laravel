<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Scroll
{
    use HasFilters;

    private int $limit = 10;
    private int|string|null $offset = null;
    private bool|array $withPayload = true;
    private bool $withVector = false;
    private ?array $orderBy = null;
    private int|string|null $nextPageOffset = null;

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points");
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int|string|null $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function withPayload(bool|array $withPayload = true): self
    {
        $this->withPayload = $withPayload;
        return $this;
    }

    public function withVector(bool $withVector = true): self
    {
        $this->withVector = $withVector;
        return $this;
    }

    public function orderBy(string $key, string $direction = 'asc'): self
    {
        $this->orderBy = ['key' => $key, 'direction' => $direction];
        return $this;
    }

    public function get(): PointsCollection
    {
        $options = [
            'limit' => $this->limit,
            'with_payload' => $this->withPayload,
            'with_vector' => $this->withVector,
        ];
        if ($this->offset !== null) {
            $options['offset'] = $this->offset;
        }
        if ($this->orderBy !== null) {
            $options['order_by'] = $this->orderBy;
        }
        if ($this->hasFilters()) {
            $options['filter'] = $this->getFilters();
        }

        $response = $this->transport->post(uri: '/scroll', options: $options);
        $result = $response->result() ?? [];
        $this->nextPageOffset = $result['next_page_offset'] ?? null;

        return PointsCollection::make($result['points'] ?? []);
    }

    public function nextPageOffset(): int|string|null
    {
        return $this->nextPageOffset;
    }
}
