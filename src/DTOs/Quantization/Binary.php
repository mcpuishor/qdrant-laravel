<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Quantization;

class Binary implements QuantizationObject {

    public function __construct(
        public bool $always_ram
    ){}

    public function toArray(): array
    {
        return [
            'binary' => [
                'always_ram' => $this->always_ram,
            ]
        ];
    }
}
