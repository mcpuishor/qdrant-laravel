<?php
namespace Mcpuishor\QdrantLaravel;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use InvalidArgumentException;
use Illuminate\Support\Traits\Macroable;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;

class QdrantSchema
{
    use Macroable;

    public function __construct(
        protected QdrantTransport $transport
    ){
        $this->transport = $this->transport->baseUri("/collections");
    }

    static public function make(?QdrantTransport $transport = null): self
    {
        return new static($transport ?? app(QdrantTransport::class));
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
            $response =  $this->transport
                        ->put(
                            uri: "/{$name}",
                             options: $vectors + $options
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

    public function exists(string $name): bool
    {
        $response = $this->transport->get( "/{$name}/exists");

        return $response->result()['exists'];
    }

    public function update(string $name, array $vectors = [], array $options = []): bool
    {
       $response = $this->transport->patch(
           uri: "/{$name}",
           options: $vectors + $options
       );

       return $response->result();
    }

    public function delete(string $name): bool
    {
        $response =  $this->transport->delete( uri: "/{$name}" );

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
