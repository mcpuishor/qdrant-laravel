<?php
namespace Mcpuishor\QdrantLaravel\Service;

use Mcpuishor\QdrantLaravel\QdrantTransport;

class Service
{
    public function __construct(
        private QdrantTransport $transport,
    ) {
        $this->transport = $this->transport->baseUri('');
    }

    public function root(): array
    {
        // The root endpoint returns a flat object (no {status,result} envelope).
        return $this->transport->get(uri: '/')->serverResponse;
    }

    public function healthz(): bool
    {
        return str_contains($this->transport->raw('/healthz'), 'passed');
    }

    public function livez(): bool
    {
        return str_contains($this->transport->raw('/livez'), 'passed');
    }

    public function readyz(): bool
    {
        return str_contains($this->transport->raw('/readyz'), 'passed');
    }

    public function telemetry(bool $anonymize = false): array
    {
        $uri = '/telemetry' . ($anonymize ? '?anonymize=true' : '');

        return $this->transport->get(uri: $uri)->result() ?? [];
    }

    public function metrics(): string
    {
        return $this->transport->raw('/metrics');
    }
}
