<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

readonly class ClusterStatus
{
    public function __construct(
        public string $status,
        public ?int $peer_id = null,
        public array $peers = [],
        public array $raft_info = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? 'unknown',
            peer_id: $data['peer_id'] ?? null,
            peers: $data['peers'] ?? [],
            raft_info: $data['raft_info'] ?? [],
        );
    }
}
