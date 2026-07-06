<?php
namespace Mcpuishor\QdrantLaravel\Traits;

use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Enums\FilterVerbs;

trait HasFilters
{
    public array $filters = [];

    public function must(string $key, FilterConditions $condition, mixed $value = null): self
    {
        return $this->addFilter(FilterVerbs::MUST, $key, $condition, $value);
    }

    public function mustNot(string $key, FilterConditions $condition, mixed $value = null): self
    {
        return $this->addFilter(FilterVerbs::MUST_NOT, $key, $condition, $value);
    }

    public function should(string $key, FilterConditions $condition, mixed $value = null): self
    {
        return $this->addFilter(FilterVerbs::SHOULD, $key, $condition, $value);
    }

    public function minShould(string $key, FilterConditions $condition, mixed $value = null, int $min_count = 1): self
    {
        $this->filters[FilterVerbs::MIN_SHOULD->value]['conditions'][] = $this->condition($key, $condition, $value);
        $this->filters[FilterVerbs::MIN_SHOULD->value]['min_count'] = $min_count;

        return $this;
    }

    private function addFilter(FilterVerbs $verb, string $key, FilterConditions $condition, mixed $value = null): self
    {
        $this->filters[$verb->value][] = $this->condition($key, $condition, $value);

        return $this;
    }

    private function condition(string $key, FilterConditions $condition, mixed $value): array
    {
        // `match` is the only clause that wraps a scalar under `value`;
        // range/geo/values_count take the operand object as-is;
        // is_empty/is_null take {key: ...} only.
        return match ($condition) {
            FilterConditions::MATCH => ['key' => $key, 'match' => ['value' => $value]],
            FilterConditions::IS_EMPTY => ['is_empty' => ['key' => $key]],
            FilterConditions::IS_NULL => ['is_null' => ['key' => $key]],
            default => ['key' => $key, $condition->value => $value],
        };
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
