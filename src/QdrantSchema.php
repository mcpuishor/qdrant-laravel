<?php
namespace Mcpuishor\QdrantLaravel;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Enums\FieldType;
use InvalidArgumentException;
use Illuminate\Support\Traits\Macroable;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;

class QdrantSchema
{
    use Macroable;

    protected QdrantClient $client;

    public function __construct(QdrantClient $client)
    {
        $this->client = $client;
    }

    static public function make(?QdrantClient $client = null): self
    {
        return new static($client ?? app(QdrantClient::class));
    }
    /**
     * @throws FailedToCreateCollectionException
     */
    public function create(string $name, array $vectors, array $options = [])
    {
        if (isset($vectors['distance'])) {
            //we're in single vector mode
            $this->validateVectorParameters($vectors);
        } else {
            //we're in multiple vectors mode
            foreach ($vectors as $vector) {
                $this->validateVectorParameters($vector);
            }
        }

        try {
            $result =  $this->client
                        ->request(
                            'PUT', "/collections/{$name}",
                             $vectors + $options
                        );
        } catch (ClientException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents());
            throw new FailedToCreateCollectionException($error->status->error, $e->getCode());
        }

        if (!isset($result['status']) || $result['status'] !== 'ok') {
            throw new FailedToCreateCollectionException( $result );
        }

        return  $result;
    }

    public function exists(string $name): bool
    {
        $result = $this->client->request('GET', "/collections/{$name}/exists");

        return $result['result']['exists'];
    }

    public function update(string $name, array $vectors = [], array $options = []): array
    {
       return  $this->client->request('PATCH', "/collections/{$name}", $vectors + $options);
    }

    public function delete(string $name): array
    {
        return $this->client->request('DELETE', "/collections/{$name}");
    }

    public function addIndex(string $collection, string $field, FieldType $type): array
    {
        return $this->client->request('PUT', "/collections/{$collection}/index", [
                'field_name' => $field,
                'field_type' => $type->value,
        ]);
    }

    public function dropIndex(string $collection, string $field)
    {
        return $this->client->request('DELETE', "/collections/{$collection}/index/{$field}");
    }

    public function collections(): Collection
    {
        $response = $this->client->request('GET', '/collections');

        return collect($response['result']['collections'] ?? [])->pluck('name');
    }

    private function validateVectorParameters(array $vector): bool
    {
        if (isset($vector['distance']) && !DistanceMetric::validate($vector['distance'])) {
            throw new InvalidArgumentException(
                "Invalid distance metric: {$vector['distance']}."
                    . " Allowed: " . implode(', ', DistanceMetric::values())
                );
        }

        if (isset($vector['size']) && $vector['size'] < 1) {
            throw new InvalidArgumentException(
                "Invalid size metric: {$vector['size']}. "
                . " Size of vector must be greater than 0."
            );
        }

        return true;
    }
}
