<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

readonly class Point
{
    public function __construct(
        public int|string $id,
        public ?array      $vector = null,
        public ?array      $payload = null,
    ){}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'vector' => $this->vector,
            'payload' => $this->payload,
        ];
    }
}
