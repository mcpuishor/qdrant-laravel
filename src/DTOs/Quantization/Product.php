<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Quantization;

class Product implements QuantizationObject {

    public function __construct(
        public string $compression,
        public bool $always_ram
    ){}

    public function toArray(): array
    {
        return [
            'product' => [
                'compression' => $this->compression,
                'always_ram' => $this->always_ram,
            ]
        ];
    }
}
