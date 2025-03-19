<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\Point;
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

    public function get(int|string|array $ids): Point|Collection
    {
        if (is_array($ids)) {
            return $this->getPointsById($ids);
        }

        return $this->find($ids);
    }

    private function getPointsById(array $ids): Collection //TODO map the result to Points and return a PointsCollection
    {
        $response = $this->transport
            ->post(
                uri: "",
                options: [
                    'json' => [
                        'ids' => $ids,
                        'with_payload' => $this->withPayload,
                        'with_vector' => $this->withVector,
                    ],
                ]
            );

        return collect($response->result() ?? []);
    }

    private function find(int $id): Point
    {
        $response = $this->transport->get( uri: "/{$id}" );

        if (!$response->isOK()) {
            return new Point($id);
        }

        return new Point(...$response->result()[0]);
    }

    public function upsert(Collection $points): bool //TODO require a PointsCollection
    {
        $response = $this->transport
            ->put(
                uri: "",
                options: [
                    'json' => [
                        'points' => $points->toArray(),
                    ],
                ]
            );

        return $response->isOk();
    }

    public function delete(array $ids): bool
    {
        $requestPayload = [
            'json' => [
                'points' => $ids,
            ]
        ];

        if ($this->getFilters()) {
            $requestPayload['json']['filter'] = $this->getFilters();
        }

        $response = $this->transport
            ->post(
                uri: "/delete",
                options: $requestPayload
            );

        return $response->isOk();
    }
}
