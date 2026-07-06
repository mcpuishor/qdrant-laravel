<?php
namespace Mcpuishor\QdrantLaravel\Snapshots;

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\SnapshotDescription;
use Mcpuishor\QdrantLaravel\Exceptions\SnapshotException;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class ShardSnapshots
{
    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
        private int $shardId,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/shards/{$this->shardId}/snapshots");
    }

    public function create(): SnapshotDescription
    {
        $response = $this->transport->post(uri: '', options: []);

        if (!$response->isOk()) {
            throw new SnapshotException($response->error() ?? 'Failed to create snapshot.');
        }

        return SnapshotDescription::fromArray($response->result());
    }

    public function list(): Collection
    {
        $result = $this->transport->get(uri: '')->result() ?? [];

        return collect($result)->map(fn (array $s) => SnapshotDescription::fromArray($s));
    }

    public function delete(string $name): bool
    {
        return $this->transport->delete(uri: "/{$name}")->isOk();
    }

    public function download(string $name): \Illuminate\Http\Client\Response
    {
        return $this->transport->download("/{$name}");
    }

    public function recover(string $location, array $options = []): bool
    {
        return $this->transport->put(uri: '/recover', options: ['location' => $location] + $options)->isOk();
    }
}
