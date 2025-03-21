<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Http\Client\Factory as Client;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Mcpuishor\QdrantLaravel\DTOs\Response;

class QdrantTransport
{
    use Macroable;

    protected string $endpoint;
    protected ?string $apiKey;

    private string $baseUri = '';

    public function __construct(
        private Client           $httpClient,
        private readonly ?string $connection = null,
    )
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

        $this->httpClient->globalOptions([
            'headers' => array_merge(
                $this->apiKey ? ['Api-key' => $this->apiKey ] : [],
                ['Content-Type' => 'application/json']
            ),
        ]);
    }

    public function self(): self
    {
        return $this;
    }

    public function baseUri(string $baseUri): self
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    public function post($uri, array $options = []): Response
    {
        return $this->request('POST', $this->baseUri . $uri, $options);
    }

    public function get($uri): Response
    {
        return $this->request('GET', $this->baseUri . $uri);
    }

    public function put($uri, array $options = []): Response
    {
        return $this->request('PUT', $this->baseUri . $uri, $options);
    }

    public function delete($uri, array $options = []): Response
    {
        if ($options !== []) {
            return $this->request('DELETE', $this->baseUri . $uri, $options);
        }

        return $this->request('DELETE', $this->baseUri . $uri);
    }

    public function patch($uri, array $options = []): Response
    {
        return $this->request('PATCH', $this->baseUri . $uri, $options);
    }

    public function collection(string $name): QdrantClient
    {
        return new QdrantClient($this, $name);
    }

    public function request(string $method, string $uri, array $options = []): Response
    {
        $response = $this->httpClient->send(
            method: $method,
            url:$this->endpoint . $uri,
            options: $options
        )->throw();

        return new Response( json_decode($response->body(), true) );
    }
}
