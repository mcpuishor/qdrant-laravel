<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

interface ConfigObject
{
    public static function fromArray(array $data): self;
    public function toArray(): array;
}