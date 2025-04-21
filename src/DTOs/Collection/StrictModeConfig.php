<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

readonly class StrictModeConfig implements ConfigObject
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

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
        ];
    }

}
