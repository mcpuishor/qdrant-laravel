<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Points
{
    use HasFilters;
    private $withPayload = true;
    private $withVector = false;

    public function __construct(
        private QdrantTransport $transport,
        private readonly string $collection,
    ){
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points" );
    }

    public function withPayload(): self
    {
        $this->withPayload = true;
        return $this;
    }

    public function withoutPayload(): self
    {
        $this->withPayload = false;
        return $this;
    }

    public function withVector(): self
    {
        $this->withVector = true;
        return $this;
    }

    public function withoutVector(): self
    {
        $this->withVector = false;
        return $this;
    }

    public function get(array $ids): Collection
    {
        if (empty($ids)) {
            throw new \InvalidArgumentException("ids must be an array");
        }

        $response = $this->transport
            ->post(
                uri: "",
                options: [
                    'ids' => $ids,
                    'with_payload' => $this->withPayload,
                    'with_vector' => $this->withVector,
                ]
            );

        return PointsCollection::make($response->result() ?? []);
    }

    public function find(int $id): Point
    {
        $response = $this->transport->get( uri: "/{$id}" );

        if (!$response->isOK()) {
            return new Point($id);
        }

        return new Point(...$response->result());
    }

    public function insert(Point $point): bool
    {
        $response = $this->transport
            ->put(
                uri: "",
                options: [
                    'points' => [
                        $point->toArray()
                    ],
                ]
            );

        return $response->isOk();
    }

    public function upsert(PointsCollection $points): bool
    {
        $response = $this->transport
            ->put(
                uri: "",
                options: [
                    'points' => $points->toArray(),
                ]
            );

        return $response->isOk();
    }

    public function delete(array $ids): bool
    {
        $requestPayload = [
            'points' => $ids,
        ];

        if ($this->getFilters()) {
            $requestPayload['filter'] = $this->getFilters();
        }

        $response = $this->transport
            ->post(
                uri: "/delete",
                options: $requestPayload
            );

        return $response->isOk();
    }

    public function autochunk(int $chunk_size = 1)
    {
        return new Autochunk($this, $chunk_size);
    }
}
