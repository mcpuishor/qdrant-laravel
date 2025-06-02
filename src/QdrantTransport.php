<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;

class QdrantTransport
{
    use Macroable;

    protected string $collection;
    protected string $endpoint;
    protected ?string $apiKey;

    private string $baseUri = '';
    private $httpClient;

    public function __construct(
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

        $this->collection = $settings['collection'] ?? null;
        $this->endpoint = $settings['host'];
        $this->apiKey = $settings['api_key'] ?? null;

        $this->httpClient = Http::baseUrl($this->endpoint)
            ->acceptJson()
            ->asJson();

        if ($this->apiKey) {
            $this->httpClient->withHeaders([
                'Api-key' => $this->apiKey ,
            ]);
        }
    }

    public function self(): self
    {
        return $this;
    }

    public function getCollection(): string
    {
        return $this->collection;
    }

    public function baseUri(string $baseUri): self
    {
        $this->baseUri = $baseUri;
        return $this;
    }

    public function post($uri, array $options = []): Response
    {
        $response = $this->httpClient->post(
                url:$this->baseUri . $uri,
                data: $options
            );

        return new Response( $response->json(), true);
    }

    public function get($uri): Response
    {
        $response = $this->httpClient->get(
                url:$this->baseUri . $uri
            );

        return new Response( $response->json(), true );
    }

    public function put($uri, array $options = []): Response
    {
        $response = $this->httpClient
            ->put(
                url:$this->baseUri . $uri,
                data: $options
        );

        if ($response->failed()) {
            throw new FailedToCreateCollectionException(
                $response->json()['status']['error'],
                $response->status()
            );
        }

        return new Response( $response->json(), true );
    }

    public function delete($uri, array $options = []): Response
    {
        if ($options !== []) {
            $response = $this->httpClient->delete(
                url:$this->baseUri . $uri,
                data: $options
            );
        } else {
            $response = $this->httpClient->delete(
                url:$this->baseUri . $uri
            );
        }
        return new Response( json_decode($response->json(), true) );
    }

    public function patch(string $uri, array $options = []): Response
    {
        $response = $this->httpClient->patch(
            url: $this->baseUri . $uri,
            data: $options
        );

        return new Response( json_decode($response->json(), true) );
    }

    public function collection(?string $name = null): QdrantClient
    {
        return new QdrantClient(
            transport: $this,
            collection: $name ?? $this->collection,
        );
    }

    public function getBaseUri()
    {
        return $this->baseUri;
    }
}