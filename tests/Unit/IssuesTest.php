<?php
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->issues = (new QdrantClient($this->transport, 'test'))->issues();
});

it('lists issues', function () {
    $this->transport->shouldReceive('get')
        ->withArgs([''])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => ['issues' => []]]));

    expect($this->issues->get())->toBe(['issues' => []]);
});

it('clears issues', function () {
    $this->transport->shouldReceive('delete')
        ->withArgs([''])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    expect($this->issues->clear())->toBeTrue();
});
