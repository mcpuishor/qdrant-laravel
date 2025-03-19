<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\Traits\Macroable;
use Mcpuishor\QdrantLaravel\Query\Indexes;
use Mcpuishor\QdrantLaravel\Query\Payloads;
use Mcpuishor\QdrantLaravel\Query\Points;
use Mcpuishor\QdrantLaravel\Query\Query;
use Mcpuishor\QdrantLaravel\Query\Vectors;

class QdrantClient
{
    use Macroable;

    protected QdrantTransport $transport;
    protected string $collection;

    public function __construct(QdrantTransport $transport, ?string $collection=null)
    {
        $this->transport = $transport;
        $this->collection = $collection;
    }

    public function schema() : QdrantSchema
    {
        return new QdrantSchema($this->transport);
    }

    public function indexes(): Indexes
    {
        return new Indexes($this->transport, $this->collection);
    }

    public function points() : Points
    {
        return new Points($this->transport, $this->collection);
    }

    public function vectors(): Vectors
    {
        return new Vectors($this->transport, $this->collection);
    }

    public function payloads()
    {
        return new Payloads($this->transport, $this->collection);
    }

    public function query(int $hnsw_ef = 128, bool $exact = false, int $limit = 10)
    {
        return new Query($this->transport, $this->collection, $hnsw_ef, $exact, $limit);
    }
}
