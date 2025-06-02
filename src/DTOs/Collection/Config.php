<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

use Mcpuishor\QdrantLaravel\DTOs\HnswConfig;

readonly class Config implements ConfigObject
{
    public function __construct(
        public Params           $params,
        public HnswConfig       $hnsw_config,
        public OptimizersConfig  $optimizers_config,
        public WalConfig        $wal_config,
        public ?array           $quantization_config,
        public StrictModeConfig $strict_mode_config,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            params: Params::fromArray($data['params']) ?? null,
            hnsw_config: HnswConfig::fromArray($data['hnsw_config']) ?? null,
            optimizers_config: OptimizersConfig::fromArray($data['optimizers_config']) ?? null,
            wal_config: WalConfig::fromArray($data['wal_config']) ?? null,
            quantization_config: $data['quantization_config'] ?? null,
            strict_mode_config: StrictModeConfig::fromArray($data['strict_mode_config']) ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'params' => $this->params->toArray(),
            'hnsw_config' => $this->hnsw_config->toArray(),
            'optimizers_config' => $this->optimizers_config->toArray(),
            'wal_config' => $this->wal_config->toArray(),
            'quantization_config' => $this->quantization_config,
            'strict_mode_config' => $this->strict_mode_config->toArray(),
        ];
    }

}
