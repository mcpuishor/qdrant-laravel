<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Filters;

use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Exceptions\InvalidFilterException;

abstract class Filter
{
    private array $conditions = [];

//    public function __construct(
//        private readonly string $condition,
//        private readonly array $values,
//    ){}

    public function get(): array
    {
        if (!$this->validate()) {
            throw new InvalidFilterException('Invalid filter definition: ' . json_encode($this->conditions));
        }

        return $this->conditions;
    }

    protected function add(FilterConditions $condition, mixed $value = null)
    {
        $this->conditions[$condition->value] = $value;
    }
    abstract function validate(): bool;
}
