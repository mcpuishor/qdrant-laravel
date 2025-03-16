<?php
namespace Mcpuishor\QdrantLaravel\Traits;

use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Enums\FilterVerbs;
use Mcpuishor\QdrantLaravel\QdrantClient;

trait HasFilters
{
    public array $filters = [];
    public function must(callable|string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        if (is_callable($key)) {
            return $this->addCallableFilter(FilterVerbs::MUST, $key);
        }

        return  $this->addSimpleFilter(FilterVerbs::MUST, $key, $condition, $value);
    }

    public function mustNot(callable|string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        if (is_callable($key)) {
            return $this->addCallableFilter(FilterVerbs::MUST_NOT, $key);
        }

        return  $this->addSimpleFilter(FilterVerbs::MUST_NOT, $key, $condition, $value);
    }

    public function should(callable|string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        if (is_callable($key)) {
            return $this->addCallableFilter(FilterVerbs::SHOULD, $key);
        }

        return  $this->addSimpleFilter(FilterVerbs::SHOULD, $key, $condition, $value);
    }

    public function minShould(string $key, ?FilterConditions $condition = null, mixed $value =  null, int $min_count = 1): self
    {
        $this->addSimpleFilter(FilterVerbs::MIN_SHOULD, $key, $condition, $value);

        $this->filters[FilterVerbs::MIN_SHOULD->value]['min_count'] = $min_count;

        return $this;
    }

    private function addCallableFilter(FilterVerbs $verb, callable $callback): self
    {
        $this->filters[$verb->value] = $callback(new QdrantClient($this->client, $this->collection))->getFilters();
        return $this;
    }

    private function addSimpleFilter(FilterVerbs $verb, string $key, FilterConditions $condition, mixed $value = null): self
    {
        $this->filters[$verb->value][] = [
            'key' => $key,
            $condition->value => [
                'value' => $value,
            ]
        ];

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
