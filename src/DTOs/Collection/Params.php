<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;
readonly class Params
{
    public function __construct(
        public array $vectors,
        public int   $shard_number,
        public int   $replication_factor,
        public int   $write_consistency_factor,
        public bool  $on_disk_payload,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vectors: $data['vectors'],
            shard_number: $data['shard_number'],
            replication_factor: $data['replication_factor'],
            write_consistency_factor: $data['write_consistency_factor'],
            on_disk_payload: $data['on_disk_payload'],
        );
    }

}
