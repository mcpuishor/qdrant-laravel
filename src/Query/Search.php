<?php
namespace Mcpuishor\QdrantLaravel\Query;
use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Search
{
    use HasFilters;
    private bool $withPayload = false;
    private array $include = [];
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

    public function withPayload( ?array $include = null, ?array $exclude = null): self
    {
        if (!empty($include)) {
            $this->include = array_merge($this->include, $include);
        }

        if(!empty($exclude)) {
            $this->exclude = array_merge($this->exclude, $exclude);
        }

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

    public function vector(array $vector): self
    {
        if (empty($vector)) {
            throw new SearchException('Search vector cannot be empty.');
        }

        $this->add( $vector );

        return  $this;
    }

    public function point(Point $point): self
    {
        if ($point->isEmpty()) {
            throw new SearchException('Search point cannot be empty.');
        }

        $this->add( $point->id() );

       return $this;
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

    public function raw(array $query): PointsCollection
    {
        $response = $this->transport->post(
            uri: $this->groupBy ? '/groups' : '',
            options: $query
        );

        if (!$response->isOK()) {
            throw new SearchException('Search could not be performed. Not a valid response returned from server.');
        }

        return PointsCollection::make($response->result() ?? []);
    }

    public function get(): PointsCollection
    {
        return $this->raw($this->getSearchPayload());
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

        if ($this->withPayload && $this->include) {
            $searchPayload['with_payload'] = [
                'only' => $this->include,
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
                'searches' => $searchPayload,
            ]
        )->result();
    }

    public function random(?int $limit): array
    {
        $payload = [
            "collection_name" => $this->collection,
            'sample' => 'random',
        ];

        if ($limit) {
            $payload['limit'] = $limit;
        }

        if ($this->withPayload) {
            $searchPayload['with_payload'] = true;
        }

        if ($this->withPayload && $this->include) {
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

        return $this->transport->post(
            uri: "",
            options: $payload,
        )->result();
    }
}
