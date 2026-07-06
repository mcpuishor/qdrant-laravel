<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\DTOs\Vector;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class NamedVectors
{
    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/vectors");
    }

    public function create(string $name, Vector|array $config): bool
    {
        $config = $config instanceof Vector ? $config->toArray() : $config;

        return $this->transport->put(uri: "/{$name}", options: $config)->isOk();
    }

    public function delete(string $name): bool
    {
        return $this->transport->delete(uri: "/{$name}")->isOk();
    }

    public function optimizations(): array
    {
        return $this->transport
            ->baseUri("/collections/{$this->collection}")
            ->get(uri: '/optimizations')->result() ?? [];
    }
}
