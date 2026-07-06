<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Matrix
{
    use HasFilters;

    private ?int $sample = null;
    private ?int $limit = null;
    private ?string $using = null;

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points/search/matrix");
    }

    public function sample(int $sample): self
    {
        $this->sample = $sample;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function using(string $using): self
    {
        $this->using = $using;
        return $this;
    }

    private function payload(): array
    {
        $payload = [];
        if ($this->sample !== null) {
            $payload['sample'] = $this->sample;
        }
        if ($this->limit !== null) {
            $payload['limit'] = $this->limit;
        }
        if ($this->using !== null) {
            $payload['using'] = $this->using;
        }
        if ($this->hasFilters()) {
            $payload['filter'] = $this->getFilters();
        }

        return $payload;
    }

    public function offsets(): array
    {
        return $this->transport->post(uri: '/offsets', options: $this->payload())->result() ?? [];
    }

    public function pairs(): array
    {
        return $this->transport->post(uri: '/pairs', options: $this->payload())->result() ?? [];
    }
}
