<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

use Mcpuishor\QdrantLaravel\DTOs\Quantization\QuantizationObject;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Enums\VectorDatatype;

readonly class Vector
{
    public function __construct(
        public int $size,
        public DistanceMetric $distanceMetric,
        public ?HnswConfig $hnsw_config = null,
        public ?QuantizationObject $quantization_config = null,
        public bool $on_disk = false,
        public ?VectorDatatype $datatype = null,
    ){}

    public function toArray(): array
    {
        $values = collect([
            'size' => $this->size,
            'distance' => $this->distanceMetric->value,
            'hnsw' => $this->hnsw_config?->toArray() ?? null,
            'quantization' => $this->quantization_config?->toArray() ?? null,
            'on_disk' => $this->on_disk,
            'datatype' => $this->datatype->value,
        ]);

        return $values->filter()->toArray();
    }
    static function fromArray($options): self
    {
        return new static(...$options);
    }
}
