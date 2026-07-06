<?php
namespace Mcpuishor\QdrantLaravel\Issues;

use Mcpuishor\QdrantLaravel\QdrantTransport;

class Issues
{
    public function __construct(
        private QdrantTransport $transport,
    ) {
        $this->transport = $this->transport->baseUri('/issues');
    }

    public function get(): array
    {
        return $this->transport->get(uri: '')->result() ?? [];
    }

    public function clear(): bool
    {
        return $this->transport->delete(uri: '')->isOk();
    }
}
