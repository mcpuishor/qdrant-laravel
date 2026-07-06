<?php
namespace Mcpuishor\QdrantLaravel\Cluster;

use Mcpuishor\QdrantLaravel\DTOs\ClusterStatus;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class Cluster
{
    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {}

    public function status(): ClusterStatus
    {
        $result = $this->transport->baseUri('/cluster')->get(uri: '')->result() ?? [];

        return ClusterStatus::fromArray($result);
    }

    public function telemetry(): array
    {
        return $this->transport->baseUri('/cluster')->get(uri: '/telemetry')->result() ?? [];
    }

    public function recover(): bool
    {
        return $this->transport->baseUri('/cluster')->post(uri: '/recover', options: [])->isOk();
    }

    public function removePeer(int $peerId, bool $force = false): bool
    {
        $uri = "/peer/{$peerId}" . ($force ? '?force=true' : '');

        return $this->transport->baseUri('/cluster')->delete(uri: $uri)->isOk();
    }

    public function collection(): array
    {
        return $this->transport->baseUri("/collections/{$this->collection}/cluster")->get(uri: '')->result() ?? [];
    }

    public function moveShard(int $shardId, int $fromPeer, int $toPeer): bool
    {
        return $this->transport->baseUri("/collections/{$this->collection}/cluster")->post(uri: '', options: [
            'move_shard' => ['shard_id' => $shardId, 'from_peer_id' => $fromPeer, 'to_peer_id' => $toPeer],
        ])->isOk();
    }

    public function replicateShard(int $shardId, int $fromPeer, int $toPeer): bool
    {
        return $this->transport->baseUri("/collections/{$this->collection}/cluster")->post(uri: '', options: [
            'replicate_shard' => ['shard_id' => $shardId, 'from_peer_id' => $fromPeer, 'to_peer_id' => $toPeer],
        ])->isOk();
    }
}
