<?php
namespace Mcpuishor\QdrantLaravel\Cluster;

use Mcpuishor\QdrantLaravel\QdrantTransport;

class Shards
{
    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/shards");
    }

    public function keys(): array
    {
        return $this->transport->get(uri: '')->result() ?? [];
    }

    public function create(string|int $shardKey, array $options = []): bool
    {
        return $this->transport->put(uri: '', options: ['shard_key' => $shardKey] + $options)->isOk();
    }

    public function delete(string|int $shardKey): bool
    {
        return $this->transport->post(uri: '/delete', options: ['shard_key' => $shardKey])->isOk();
    }
}
