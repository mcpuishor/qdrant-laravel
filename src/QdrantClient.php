<?php
namespace Mcpuishor\QdrantLaravel;

use GuzzleHttp\Client;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class QdrantClient
{
    use Macroable;

    protected Client $httpClient;
    protected string $endpoint;
    protected ?string $apiKey;

    public function __construct(string $connection = null)
    {
        $config = config('qdrant-laravel');

        $connections = $config['connections'] ?? [];
        $connection = $connection ?? ($config['default'] ?? 'main');

        if (!isset($connections[$connection])) {
            throw new InvalidArgumentException("Qdrant connection [$connection] not defined.");
        }

        $settings = $connections[$connection];
        $this->endpoint = $settings['host'];
        $this->apiKey = $settings['api_key'] ?? null;

        $this->httpClient = new Client([
            'headers' => array_merge(
                $this->apiKey ? ['Api-key' => $this->apiKey ] : [],
                ['Content-Type' => 'application/json']
            ),
        ]);
    }

    public function get(): self
    {
        return $this;
    }

    public function collection(string $name): QdrantQueryBuilder
    {
        return new QdrantQueryBuilder($this, $name);
    }

    public function request(string $method, string $uri, array $options = []): array
    {
        $response = $this->httpClient->request($method, $this->endpoint . $uri, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
