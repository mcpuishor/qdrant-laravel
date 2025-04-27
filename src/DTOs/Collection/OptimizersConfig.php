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
        public ?int $flush_interval_sec = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deleted_threshold: $data['deleted_threshold'] ?? null,
            vacuum_min_vector_number: $data['vacuum_min_vector_number'] ?? null,
            default_segment_number: $data['default_segment_number'] ?? null,
            max_segment_size: $data['max_segment_size'] ?? null,
            memmap_threshold: $data['memmap_threshold'] ?? null,
            indexing_threshold: $data['indexing_threshold'] ?? null,
            max_optimization_threads: $data['max_optimization_threads'] ?? null,
            flush_interval_sec: $data['flush_interval_sec'] ?? null,
        );
    }

    public function toArray(): array
    {
        $array = [
            'deleted_threshold' => $this->deleted_threshold,
            'vacuum_min_vector_number' => $this->vacuum_min_vector_number,
            'default_segment_number' => $this->default_segment_number,
            'max_segment_size' => $this->max_segment_size,
            'memmap_threshold' => $this->memmap_threshold,
            'indexing_threshold' => $this->indexing_threshold,
            'max_optimization_threads' => $this->max_optimization_threads,
            'flush_interval_sec' => $this->flush_interval_sec,
        ];
    
        return array_filter($array, fn($value) => $value !== null);
    }
}
