<?php
namespace Mcpuishor\QdrantLaravel\Schema;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Mcpuishor\QdrantLaravel\DTOs\Collection\ConfigObject;
use Mcpuishor\QdrantLaravel\DTOs\Collection\Info;
use Mcpuishor\QdrantLaravel\DTOs\Vector;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;
use Mcpuishor\QdrantLaravel\QdrantTransport;

class Schema
{
    use Macroable;

    public function __construct(
        protected QdrantTransport $transport
    ){
        $this->transport = $this->transport->baseUri("/collections");
    }

    static public function connection(?string $connection = null): self
    {
        return new static(
            $connection !== null
                ? new QdrantTransport($connection)
                : app(QdrantTransport::class)
        );
    }

    /**
     * @throws FailedToCreateCollectionException
     */
    public function create(string $name, Vector|array $vectors, array $options = [])
    {
        if ( $vectors instanceof Vector ) {
            $this->validateVectorParameters($vectors->toArray());;
        } else if (isset($vectors['distance'])) {
            //we're in single vector mode
            $this->validateVectorParameters($vectors);
        } else {
            //we're in multiple vectors mode
            foreach ($vectors as $vector) {
                $this->validateVectorParameters(
                    vector: ($vector instanceof Vector)
                        ? $vector->toArray()
                        : $vector
                );
            }
        }

        if(!empty($options)) {
            collect($options)->flatMap(function($value, $key) {
                if ($value instanceof ConfigObject) {
                    return [ $key => $value->toArray() ];
                }

                return [$key => $value];
            });
        }

        try {
            $response =  $this->transport
                    ->put(
                        uri: "/{$name}",
                         options: [
                             'vectors' => (array) $vectors,
                             ...$options,
                        ]
                    );
        } catch (ClientException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents());
            throw new FailedToCreateCollectionException($error->status->error, $e->getCode());
        }

        if (!$response->isOK()) {
            throw new FailedToCreateCollectionException( $response->result() );
        }

        return  $response->result();
    }

    public function collections(): Collection
    {
        $response = $this->transport->get( '' );

        return collect($response->result()['collections'] ?? [])->pluck('name');
    }

    public function exists(?string $name= null): bool
    {
        $name = $name ?? $this->transport->getCollection();

        $response = $this->transport->get( "/{$name}/exists");

        return $response->result()['exists'] ?? throw new InvalidArgumentException("Error in response from Qdrant server.");
    }

    public function update(?string $collectionName=null, array $vectors = [], array $options = []): bool
    {
        if (empty($vectors) && empty($options)) {
            throw new InvalidArgumentException("Vectors or Options must be provided when trying to update a collection.");
        }
        $collectionName = $collectionName ?? $this->transport->getCollection();
        $vectors = collect($vectors)->flatMap(function ($vector, $key) {
            if ($vector instanceof Vector) {
                return [$key => $vector->toArray()];
            }
            return [$key => $vector];
        })->toArray();

        $options = collect($options)
            ->flatMap(function($configObject){
                if (!$configObject instanceof ConfigObject) {
                    return;
                }
                $class = explode('\\', get_class($configObject));
                $name = str()->snake(end($class));

                return [
                    $name => $configObject->toArray(),
                ];
            })->toArray();

        if (!empty($vectors)) {
            $options['vectors'] = $vectors;
        }

       $response = $this->transport->patch(
           uri: "/{$collectionName}",
           options: $options
       );

       return $response->result();
    }

    public function delete(string $collectionName): bool
    {
        $response =  $this->transport->delete( uri: "/{$collectionName}" );

        return $response->result();
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
