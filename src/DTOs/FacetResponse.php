<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

readonly class FacetResponse
{
    public function __construct(public array $hits) {}

    public static function fromArray(array $result): self
    {
        return new self($result['hits'] ?? []);
    }

    public function hits(): array
    {
        return $this->hits;
    }
}
