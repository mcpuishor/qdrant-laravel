<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

readonly class StrictModeConfig
{
    public function __construct(
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'],
        );
    }

}
