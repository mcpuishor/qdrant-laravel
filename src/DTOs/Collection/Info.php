<?php
namespace Mcpuishor\QdrantLaravel\DTOs\Collection;

readonly class Info
{
    public function __construct(
        public string $status,
        public string $optimizer_status,
        public int    $indexed_vectors_count,
        public int    $points_count,
        public int    $segments_count,
        public Config $config,
        public array  $payload_schema,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'],
            optimizer_status: $data['optimizer_status'],
            indexed_vectors_count: $data['indexed_vectors_count'],
            points_count: $data['points_count'],
            segments_count: $data['segments_count'],
            config: Config::fromArray($data['config']),
            payload_schema: $data['payload_schema'],
        );
    }

    public function isReady(): bool
    {
        return $this->status === 'green';
    }

    public function isOptimizing(): bool
    {
        return $this->status === 'yellow';
    }

    public function isPending(): bool
    {
        return $this->status === 'grey';
    }

    public function isError(): bool
    {
        return $this->status === 'red';
    }
}

