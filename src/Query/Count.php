<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Count
{
    use HasFilters;

    private bool $exact = false;

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points");
    }

    public function exact(bool $exact = true): self
    {
        $this->exact = $exact;
        return $this;
    }

    public function get(): int
    {
        $options = ['exact' => $this->exact];
        if ($this->hasFilters()) {
            $options = ['filter' => $this->getFilters()] + $options;
        }

        $response = $this->transport->post(uri: '/count', options: $options);

        return (int) ($response->result()['count'] ?? 0);
    }
}
