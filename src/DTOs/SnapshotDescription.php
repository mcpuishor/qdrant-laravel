<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

readonly class SnapshotDescription
{
    public function __construct(
        public string $name,
        public ?string $creation_time = null,
        public ?int $size = null,
        public ?string $checksum = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            creation_time: $data['creation_time'] ?? null,
            size: $data['size'] ?? null,
            checksum: $data['checksum'] ?? null,
        );
    }
}
