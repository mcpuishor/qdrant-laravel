<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

readonly class HnswConfig
{
    public function __construct(
        public ?int  $m = null,
        public ?int  $ef_construct = null,
        public ?int  $full_scan_threshold = null,
        public ?int  $max_indexing_threads = null,
        public ?bool $on_disk = null,
        public ?int $payload_m = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            m: $data['m'] ?? null,
            ef_construct: $data['ef_construct'] ?? null,
            full_scan_threshold: $data['full_scan_threshold'] ?? null,
            max_indexing_threads: $data['max_indexing_threads'] ?? null,
            on_disk: $data['on_disk'] ?? false,
            payload_m: $data['payload_m'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'm' => $this->m,
            'ef_construct' => $this->ef_construct,
            'full_scan_threshold' => $this->full_scan_threshold,
            'max_indexing_threads' => $this->max_indexing_threads,
            'on_disk' => $this->on_disk,
            'payload_m' => $this->payload_m,
        ];
    }

}
