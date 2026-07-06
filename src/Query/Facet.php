<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\DTOs\FacetResponse;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Facet
{
    use HasFilters;

    private ?int $limit = null;
    private bool $exact = false;

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
        private string $key,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}");
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function exact(bool $exact = true): self
    {
        $this->exact = $exact;
        return $this;
    }

    public function get(): FacetResponse
    {
        $options = ['key' => $this->key];
        if ($this->limit !== null) {
            $options['limit'] = $this->limit;
        }
        $options['exact'] = $this->exact;
        if ($this->hasFilters()) {
            $options['filter'] = $this->getFilters();
        }

        return FacetResponse::fromArray(
            $this->transport->post(uri: '/facet', options: $options)->result() ?? []
        );
    }
}
