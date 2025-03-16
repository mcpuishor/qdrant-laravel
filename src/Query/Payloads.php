<?php
namespace Mcpuishor\QdrantLaravel\Query;

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Traits\HasFilters;

class Payloads
{
    use HasFilters;

    private array $points = [];
    private string $endpoint;

    public function __construct(
        private readonly QdrantTransport $transport,
        private readonly string          $collection,
    ){
        $this->endpoint = "/collections/{$this->collection}/points/payload";
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
        return $this->transport->request(
            method: 'POST',
            uri: $this->endpoint . '/delete',
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
        return $this->transport->request(
            method: 'POST',
            uri: $this->endpoint . '/clear',
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
            uri: $this->endpoint,
            options: [
                'json' => [
                    'points' => $this->points,
                    'payload' => $payload,
                ]
            ]
        )->isOk();
    }
}
