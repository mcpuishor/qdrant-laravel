<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Payloads
{
    use HasFilters;

    private array $points = [];

    public function __construct(
        private QdrantTransport $transport,
        private string $collection,
    ){
        $this->transport = $this->transport->baseUri("/collections/{$this->collection}/points/payload");
    }

    public function for( array|PointsCollection $points ) :self
    {
        if (is_array($points)) {
            $this->points  = $points;
        } else {
            $this->points = $points->pluck('id')->toArray();
        }

        return $this;
    }
    public function set(array $payload): bool
    {
        return $this->sendRequest('POST', $payload);
    }

    public function overwrite(array $payload): bool
    {
        return $this->sendRequest('PUT', $payload);
    }

    public function clear(array $keys): bool
    {
        return $this->transport->post(
            uri: '/delete',
            options: [
                'json' => [
                    'points' => $this->points,
                    'keys' => $keys,
                ]
            ]
        )->isOk();
    }

    public function clearAll(): bool
    {
        return $this->transport->post(
            uri: '/clear',
            options: [
                'json' => [
                    'points' => $this->points,
                ]
            ]
        )->isOk();
    }

    private function sendRequest(string $method, $payload)
    {
        return $this->transport->request(
            method: $method,
            uri: '',
            options: [
                'json' => [
                    'points' => $this->points,
                    'payload' => $payload,
                ]
            ]
        )->isOk();
    }
}
