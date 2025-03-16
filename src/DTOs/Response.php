<?php
namespace Mcpuishor\QdrantLaravel\DTOs;

use Mcpuishor\QdrantLaravel\Enums\ServerResponseStatus;

readonly class Response
{
    public function __construct(
        public readonly array $serverResponse,
    ){}

    public function isOk(): bool
    {
        return isset($this->serverResponse['status']) &&
            $this->serverResponse['status'] == ServerResponseStatus::OK->value;
    }

    public function time(): float
    {
        return $this->serverResponse['time'];
    }

    public function usage(): string
    {
        return $this->serverResponse['usage'];
    }

    public function result()
    {
        return $this->serverResponse['result'];
    }

}
