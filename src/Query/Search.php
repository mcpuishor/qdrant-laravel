<?php
namespace Mcpuishor\QdrantLaravel\Query;
use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Search
{
    use HasFilters;
    private bool $withPayload = false;
    private array $only = [];
    private array $exclude = [];
    private bool $withVectors = false;

    public function __construct(
        private QdrantTransport $transport,
        private readonly string $collection,
        private readonly int $hnsw_ef,
        private readonly bool $exact,
        private int $limit,
    ){}

    public function include( string|array $only = []): self
    {
        if ($only) {
            $this->only = array_merge($this->only, $only);
        }

        return $this->withPayload();
    }

    public function exclude(string|array $exclude = []): self
    {
        if ($exclude) {
            $this->exclude = array_merge($this->exclude, $exclude);
        }

        return  $this->withPayload();
    }

    public function withPayload(): self
    {
        $this->withPayload  = true;

        return $this;
    }

    public function withVectors(): self
    {
        $this->withVectors  = true;

        return $this;
    }

    public function limit(int $limit): self
    {
        if ($limit < 1) {
            throw new SearchException('Limit must be greater than 0.');
        }

        $this->limit = $limit;

        return $this;
    }

    public function vector(array $vector): array
    {
        if ($vector === []) {
            throw new SearchException('Search vector cannot be empty.');
        }

        $result = $this->performSearch($vector);

        if (!$result->isOK()) {
            throw new SearchException('Search could not be performed.'); //TODO add a more explicit message to this exception
        }
        return $result->result();
    }

    public function point(Point $point): array
    {
        if ($point->isEmpty()) {
            throw new SearchException('Search point cannot be empty.');
        }

        $result = $this->performSearch($point->id());

        if (!$result->isOK()) {
            throw new SearchException('Search could not be performed.'); //TODO add a more explicit message to this exception
        }
        return $result->result();
    }

    private function performSearch(array|string $query): Response
    {
        $searchPayload = [
            "query" => $query,
            "params" => [
                "hnsw_ef" => $this->hnsw_ef,
                "exact" => $this->exact,
            ],
            "limit" => $this->limit,
        ];

        if ($this->withPayload) {
            $searchPayload['with_payload'] = [
                'exclude' => $this->exclude,
                'only' => $this->only,
            ];
        }

        if ($this->getFilters()) {
            $searchPayload['filter'] = $this->getFilters();
        }

        return $this->transport->request(
            method: 'POST',
            uri: "/collections/{$this->collection}/points/query",
            options: [
                'json' => $searchPayload,
            ]
        );
    }
}
