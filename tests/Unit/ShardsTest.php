<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->shards = (new QdrantClient($this->transport, 'test'))->shards();
});

it('creates a shard key', function () {
    $this->transport->shouldReceive('put')
        ->withArgs(['', ['shard_key' => 'region-eu']])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    expect($this->shards->create('region-eu'))->toBeTrue();
});

it('deletes a shard key', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['/delete', ['shard_key' => 'region-eu']])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    expect($this->shards->delete('region-eu'))->toBeTrue();
});
