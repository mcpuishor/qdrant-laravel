<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

use Mcpuishor\QdrantLaravel\Enums\ServerResponseStatus;

readonly class Response
{
    public function __construct(
        public array $serverResponse,
    ) {}

    public function isOk(): bool
    {
        return ($this->serverResponse['status'] ?? null) === ServerResponseStatus::OK->value;
    }

    public function status(): string
    {
        $status = $this->serverResponse['status'] ?? 'unknown';

        return is_array($status) ? ($status['error'] ?? 'error') : (string) $status;
    }

    public function error(): ?string
    {
        $status = $this->serverResponse['status'] ?? null;

        return is_array($status) ? ($status['error'] ?? null) : null;
    }

    public function time(): float
    {
        return (float) ($this->serverResponse['time'] ?? 0.0);
    }

    public function usage(): ?array
    {
        return $this->serverResponse['usage'] ?? null;
    }

    public function result(): mixed
    {
        return $this->serverResponse['result'] ?? null;
    }
}
