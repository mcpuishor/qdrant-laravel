<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

readonly class OptimizersConfig implements ConfigObject
{
    public function __construct(
        public ?float $deleted_threshold = null,
        public ?int $vacuum_min_vector_number = null,
        public ?int $default_segment_number = null,
        public ?int $max_segment_size = null,
        public ?int $memmap_threshold = null,
        public ?int $indexing_threshold = null,
        public ?int $max_optimization_threads = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deleted_threshold: (float) ($data['deleted_threshold'] ?? null),
            vacuum_min_vector_number: (int) ($data['vacuum_min_vector_number'] ?? null),
            default_segment_number: (int) ($data['default_segment_number'] ?? null),
            max_segment_size: (int) ($data['max_segment_size'] ?? null),
            memmap_threshold: (int) ($data['memmap_threshold'] ?? null),
            indexing_threshold: (int) ($data['indexing_threshold'] ?? null),
            max_optimization_threads: (int) ($data['max_optimization_threads'] ?? null),
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
            'indexing_threshold' => $this->indexing_threshold,
            'max_optimization_threads' => $this->max_optimization_threads,
        ];
    }
}