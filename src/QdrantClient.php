<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\Traits\Macroable;
use Mcpuishor\QdrantLaravel\Query\Indexes;
use Mcpuishor\QdrantLaravel\Query\Payloads;
use Mcpuishor\QdrantLaravel\Query\Points;
use Mcpuishor\QdrantLaravel\Query\Search;
use Mcpuishor\QdrantLaravel\Query\Vectors;
use Mcpuishor\QdrantLaravel\Schema\Alias;
use Mcpuishor\QdrantLaravel\Schema\Info;
use Mcpuishor\QdrantLaravel\Schema\Schema;

class QdrantClient
{
    use Macroable;

    protected QdrantTransport $transport;
    protected ?string $collection;

    public function __construct(QdrantTransport $transport, ?string $collection=null)
    {
        $this->transport = $transport;
        $this->collection = $collection;
    }

    public function collection(string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function info()
    {
        return new Info($this->transport, $this->collection);
    }

    public function schema() : Schema
    {
        return new Schema($this->transport);
    }

    public function aliases(): Alias
    {
        return new Alias($this->transport);
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

    public function search(int $hnsw_ef = 128, bool $exact = false, int $limit = 10)
    {
        return new Search($this->transport, $this->collection, $hnsw_ef, $exact, $limit);
    }
}
