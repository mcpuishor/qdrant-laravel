<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Discover
{
    use HasFilters;

    private mixed $target = null;
    private array $context = [];
    private ?string $using = null;
    private int $limit = 10;
    private ?int $offset = null;
    private bool|array $withPayload = false;
    private bool $withVector = false;

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points/discover");
    }

    public function target(mixed $target): self
    {
        $this->target = $target;
        return $this;
    }

    public function context(array $pairs): self
    {
        $this->context = $pairs;
        return $this;
    }

    public function using(string $using): self
    {
        $this->using = $using;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
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

    public function toArray(): array
    {
        $payload = ['limit' => $this->limit];
        if ($this->target !== null) {
            $payload['target'] = $this->target;
        }
        if ($this->context) {
            $payload['context'] = $this->context;
        }
        if ($this->using) {
            $payload['using'] = $this->using;
        }
        if ($this->offset !== null) {
            $payload['offset'] = $this->offset;
        }
        if ($this->withPayload !== false) {
            $payload['with_payload'] = $this->withPayload;
        }
        if ($this->withVector) {
            $payload['with_vector'] = true;
        }
        if ($this->hasFilters()) {
            $payload['filter'] = $this->getFilters();
        }

        // discover requires at least a target or a non-empty context
        if ($this->target === null && !$this->context) {
            throw new SearchException('Discover requires a target or context.');
        }

        // move target/context to the front for a stable, readable payload
        $ordered = [];
        foreach (['target', 'context'] as $k) {
            if (array_key_exists($k, $payload)) {
                $ordered[$k] = $payload[$k];
                unset($payload[$k]);
            }
        }

        return $ordered + $payload;
    }

    public function get(): PointsCollection
    {
        $response = $this->transport->post(uri: '', options: $this->toArray());

        if (!$response->isOk()) {
            throw new SearchException($response->error() ?? 'Discover request failed.');
        }

        return PointsCollection::make($response->result() ?? []);
    }

    public function batch(array $discovers): array
    {
        throw_if(count($discovers) === 0, SearchException::class, 'Discover batch cannot be empty.');

        $searches = [];
        foreach ($discovers as $d) {
            throw_if(!$d instanceof self, SearchException::class, 'Each discover must be a Discover instance.');
            $searches[] = $d->toArray();
        }

        return $this->transport->post(uri: '/batch', options: ['searches' => $searches])->result() ?? [];
    }
}
