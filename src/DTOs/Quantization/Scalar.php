<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Quantization;

class Scalar implements QuantizationObject {

    public function __construct(
        public int $type,
        public float $quantile,
        public bool $always_ram
    ){}

    public function toArray(): array
    {
        return [
            'scalar' => [
                'type' => $this->type,
                'quantile' => $this->quantile,
                'always_ram' => $this->always_ram,
            ]
        ];
    }
}
