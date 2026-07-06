<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\DTOs\SnapshotDescription;
use Mcpuishor\QdrantLaravel\Exceptions\SnapshotException;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->snapshots = (new QdrantClient($this->transport, 'test'))->shardSnapshots(0);
});

it('creates a shard snapshot', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', []])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'name' => 'shard-0.snapshot', 'creation_time' => null, 'size' => 2,
        ]]));

    $snap = $this->snapshots->create();

    expect($snap)->toBeInstanceOf(SnapshotDescription::class)
        ->and($snap->name)->toBe('shard-0.snapshot');
});

it('throws a SnapshotException when creation fails', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', []])
        ->andReturn(new Response(['status' => ['error' => 'boom'], 'time' => 0.0]));

    expect(fn () => $this->snapshots->create())->toThrow(SnapshotException::class);
});

it('lists shard snapshots', function () {
    $this->transport->shouldReceive('get')
        ->withArgs([''])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            ['name' => 'a.snapshot', 'creation_time' => null, 'size' => 1],
        ]]));

    expect($this->snapshots->list())->toHaveCount(1);
});

it('deletes a shard snapshot', function () {
    $this->transport->shouldReceive('delete')
        ->withArgs(['/a.snapshot'])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0]));

    expect($this->snapshots->delete('a.snapshot'))->toBeTrue();
});

it('recovers a shard snapshot', function () {
    $this->transport->shouldReceive('put')
        ->withArgs(['/recover', ['location' => 'file:///tmp/a.snapshot']])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0]));

    expect($this->snapshots->recover('file:///tmp/a.snapshot'))->toBeTrue();
});
