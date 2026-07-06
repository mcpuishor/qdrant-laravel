<?php

use Mcpuishor\QdrantLaravel\DTOs\ClusterStatus;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->cluster = (new QdrantClient($this->transport, 'test'))->cluster();
});

it('reads cluster status', function () {
    $this->transport->shouldReceive('get')
        ->withArgs([''])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'status' => 'enabled', 'peer_id' => 123, 'peers' => [], 'raft_info' => [],
        ]]));

    $status = $this->cluster->status();

    expect($status)->toBeInstanceOf(ClusterStatus::class)
        ->and($status->peer_id)->toBe(123);
});

it('moves a shard between peers', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', ['move_shard' => ['shard_id' => 1, 'from_peer_id' => 10, 'to_peer_id' => 20]]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    expect($this->cluster->moveShard(1, 10, 20))->toBeTrue();
});
