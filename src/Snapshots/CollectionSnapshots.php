<?php
namespace Mcpuishor\QdrantLaravel\Snapshots;

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\SnapshotDescription;
use Mcpuishor\QdrantLaravel\Exceptions\SnapshotException;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class CollectionSnapshots
{
    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/snapshots");
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

    public function upload(string $filePath, array $options = []): bool
    {
        throw new SnapshotException(
            'Multipart snapshot upload is not yet supported by this package. '
            . 'Use recover() with a location the Qdrant server can access.'
        );
    }
}
