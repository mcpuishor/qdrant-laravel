<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

use Mcpuishor\QdrantLaravel\DTOs\HnswConfig;

readonly class Config
{
    public function __construct(
        public Params           $params,
        public HnswConfig       $hnsw_config,
        public OptimizerConfig  $optimizer_config,
        public WalConfig        $wal_config,
        public ?array           $quantization_config,
        public StrictModeConfig $strict_mode_config,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            params: Params::fromArray($data['params']),
            hnsw_config: HnswConfig::fromArray($data['hnsw_config']),
            optimizer_config: OptimizerConfig::fromArray($data['optimizer_config']),
            wal_config: WalConfig::fromArray($data['wal_config']),
            quantization_config: $data['quantization_config'],
            strict_mode_config: StrictModeConfig::fromArray($data['strict_mode_config']),
        );
    }

}
