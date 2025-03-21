<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

readonly class HnswConfig
{
    public function __construct(
        public int  $m,
        public int  $ef_construct,
        public int  $full_scan_threshold,
        public int  $max_indexing_threads,
        public bool $on_disk,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            m: $data['m'],
            ef_construct: $data['ef_construct'],
            full_scan_threshold: $data['full_scan_threshold'],
            max_indexing_threads: $data['max_indexing_threads'],
            on_disk: $data['on_disk'],
        );
    }

}
