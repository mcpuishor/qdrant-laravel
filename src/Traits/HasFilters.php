<?php
namespace Mcpuishor\QdrantLaravel\Traits;

use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Enums\FilterVerbs;
use Mcpuishor\QdrantLaravel\QdrantClient;

trait HasFilters
{
    public array $filters = [];
    public function must(string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        return  $this->addFilter(FilterVerbs::MUST, $key, $condition, $value);
    }

    public function mustNot(string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        return  $this->addFilter(FilterVerbs::MUST_NOT, $key, $condition, $value);
    }

    public function should(string $key, ?FilterConditions $condition = null, mixed $value =  null): self
    {
        return  $this->addFilter(FilterVerbs::SHOULD, $key, $condition, $value);
    }

    public function minShould(string $key, ?FilterConditions $condition = null, mixed $value =  null, int $min_count = 1): self
    {
        $this->addFilter(FilterVerbs::MIN_SHOULD, $key, $condition, $value);
        $this->filters[FilterVerbs::MIN_SHOULD->value]['min_count'] = $min_count;

        return $this;
    }

    private function addFilter(FilterVerbs $verb, string $key, FilterConditions $condition, mixed $value = null): self
    {
        $this->filters[$verb->value][] = [
            'key' => $key,
            $condition->value => [
                'value' => $value,
            ]
        ];

        return $this;
    }

    public function hasFilters(): bool
    {
        return count($this->filters) > 0;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
