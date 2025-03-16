<?php
namespace Mcpuishor\QdrantLaravel;

class Filter
{
    private array $filters = [];
    public function __construct(){}

    public function should(array $condition): self
    {
        $this->filters['should'] = $condition;

        return $this;
    }

    public function must(array $condition): self
    {
        $this->filters['must'] = $condition;

        return $this;
    }

    public function mustNot(array $condition): self
    {
        $this->filters['must_not'] = $condition;

        return $this;
    }

    public function min_should(array $condition): self
    {
        $this->filters['min_should'] = $condition;

        return $this;
    }

    public function get(): array
    {
        return $this->filters;
    }

}
