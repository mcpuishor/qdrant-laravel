<?php
namespace Mcpuishor\QdrantLaravel\Schema;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use \Mcpuishor\QdrantLaravel\DTOs\Collection\Info as InfoDTO;

class Info
{

    public function __construct(
        protected QdrantTransport $transport,
        private string $collection
    ){
        $this->transport = $this->transport->baseUri("/collections");
    }

    public function get(): InfoDTO
    {
        $response = $this->transport->get( "/{$this->collection}");

        return InfoDTO::fromArray($response->result());
    }
}
