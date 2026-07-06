<?php
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->service = (new QdrantClient($this->transport, 'test'))->service();
});

it('returns root instance info', function () {
    $this->transport->shouldReceive('get')
        ->withArgs(['/'])
        ->andReturn(new Response(['title' => 'qdrant', 'version' => '1.18.0']));

    // root() returns the decoded body directly (no result wrapper)
    expect($this->service->root())->toBe(['title' => 'qdrant', 'version' => '1.18.0']);
});

it('reports healthz via the raw endpoint', function () {
    $this->transport->shouldReceive('raw')
        ->withArgs(['/healthz'])
        ->andReturn('healthz check passed');

    expect($this->service->healthz())->toBeTrue();
});
