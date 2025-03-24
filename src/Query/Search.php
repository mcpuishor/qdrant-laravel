<?php
namespace Mcpuishor\QdrantLaravel\Query;
use Mcpuishor\QdrantLaravel\DTOs\Point;
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
    protected string|array $query;

    private int $offset = 0;
    private array $groupBy = [];

    private ?string $using = null;

    public function __construct(
        protected QdrantTransport $transport,
        protected string $collection,
        protected int $hnsw_ef,
        protected bool $exact,
        protected int $limit,
    ){
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points/query");
    }

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

    public function offset($startOffset = 0): self
    {
        $this->offset = $startOffset;

        return $this;
    }

    public function using(string $using): self
    {
        $this->using = $using;
        return $this;
    }

    public function vector(array $vector): array
    {
        if ($vector === []) {
            throw new SearchException('Search vector cannot be empty.');
        }

        $this->add( $vector );

        return  $this->performSearch();
    }

    public function point(Point $point): array
    {
        if ($point->isEmpty()) {
            throw new SearchException('Search point cannot be empty.');
        }

        $this->add( $point->id() );

       return $this->performSearch();
    }

    public function groupBy(string $payloadKey, int $groupSize = 100, array $withLookup = []): self
    {
        $this->groupBy = [
            'group_by' => $payloadKey,
            'group_size' => $groupSize,
        ];

        if ($withLookup) {
            $this->groupBy['with_lookup'] = $withLookup;
        }

        return $this;
    }

    protected function performSearch(): array
    {
        $result = $this->transport->post(
            uri: $this->groupBy ? '/groups' : '',
            options: [
                'json' => $this->getSearchPayload(),
            ]
        );

        if (!$result->isOK()) {
            throw new SearchException('Search could not be performed. Not a valid response returned from server.');
        }

        return $result->result();
    }

    public function getSearchPayload(): array
    {
        $searchPayload = [
            "query" => $this->query,
            "params" => [
                "hnsw_ef" => $this->hnsw_ef,
                "exact" => $this->exact,
            ],
            "limit" => $this->limit,
        ];

        if($this->using) {
            $searchPayload['using'] = $this->using;
        }

        if ($this->withPayload) {
            $searchPayload['with_payload'] = true;
        }

        if ($this->withPayload && $this->only) {
            $searchPayload['with_payload'] = [
                'only' => $this->only,
            ];
        }

        if ($this->withPayload && $this->exclude) {
            $searchPayload['with_payload'] = [
                'exclude' => $this->exclude,
            ];
        }

        if($this->withVectors) {
            $searchPayload['with_vectors'] = true;
        }

        if ($this->getFilters()) {
            $searchPayload['filter'] = $this->getFilters();
        }

        if ($this->groupBy) {
            $searchPayload = array_merge($searchPayload, $this->groupBy);
        }

        if ($this->offset > 0 && !$this->groupBy) {
            $searchPayload['offset'] = $this->offset;
        }

        return $searchPayload;
    }

    public function add(array|string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function batch(array $searches): array
    {
        throw_if(count($searches) === 0, SearchException::class, 'Search array cannot be empty.');

        $searchPayload = [];

        foreach($searches as $search) {
            throw_if(!$search instanceof Search, SearchException::class, 'Search must be an instance of Search.');
            $searchPayload[] = $search->getSearchPayload();
        }

        return $this->transport->post(
            uri: "/batch",
            options: [
                'json' => [
                    'searches' => $searchPayload,
                ],
            ]
        )->result();
    }

    public function random(): array
    {
        return $this->transport->post(
            uri: "",
            options: [
                'json' => [
                    "collection_name" => $this->collection,
                    'sample' => 'random',
                ]
            ]
        )->result();
    }
}
