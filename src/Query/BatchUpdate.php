<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class BatchUpdate
{
    private array $operations = [];

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ) {
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points");
    }

    public function upsert(PointsCollection $points): self
    {
        $this->operations[] = ['upsert' => ['points' => $points->toArray()]];
        return $this;
    }

    public function deletePoints(array $ids): self
    {
        $this->operations[] = ['delete' => ['points' => $ids]];
        return $this;
    }

    public function setPayload(array $payload, array $points): self
    {
        $this->operations[] = ['set_payload' => ['payload' => $payload, 'points' => $points]];
        return $this;
    }

    public function overwritePayload(array $payload, array $points): self
    {
        $this->operations[] = ['overwrite_payload' => ['payload' => $payload, 'points' => $points]];
        return $this;
    }

    public function deletePayload(array $keys, array $points): self
    {
        $this->operations[] = ['delete_payload' => ['keys' => $keys, 'points' => $points]];
        return $this;
    }

    public function clearPayload(array $points): self
    {
        $this->operations[] = ['clear_payload' => ['points' => $points]];
        return $this;
    }

    public function updateVectors(PointsCollection $points): self
    {
        $this->operations[] = ['update_vectors' => ['points' => $points->toArray()]];
        return $this;
    }

    public function deleteVectors(array $ids, array $vectorNames): self
    {
        $this->operations[] = ['delete_vectors' => ['points' => $ids, 'vector' => $vectorNames]];
        return $this;
    }

    public function execute(): bool
    {
        return $this->transport->post(
            uri: '/batch',
            options: ['operations' => $this->operations],
        )->isOk();
    }
}
