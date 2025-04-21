<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

readonly class OptimizerConfig implements ConfigObject
{
    public function __construct(
        public float $deleted_threshold,
        public int   $vacuum_min_vector_number,
        public int   $default_segment_number,
        public ?int  $max_segment_size,
        public ?int  $memmap_threshold,
        public int   $indexing_threshold,
        public ?int  $max_optimization_threads,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deleted_threshold: $data['deleted_threshold'],
            vacuum_min_vector_number: $data['vacuum_min_vector_number'],
            default_segment_number: $data['default_segment_number'],
            max_segment_size: $data['max_segment_size'],
            memmap_threshold: $data['memmap_threshold'],
            indexing_threshold: $data['indexing_threshold'],
            max_optimization_threads: $data['max_optimization_threads'],
        );
    }

    public function toArray(): array
    {
        return [
            'deleted_threshold' => $this->deleted_threshold,
            'vacuum_min_vector_number' => $this->vacuum_min_vector_number,
            'default_segment_number' => $this->default_segment_number,
            'max_segment_size' => $this->max_segment_size,
            'memmap_threshold' => $this->memmap_threshold,
        ];
    }

}
