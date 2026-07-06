<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\DTOs\SnapshotDescription;
use Mcpuishor\QdrantLaravel\Exceptions\SnapshotException;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->snapshots = (new QdrantClient($this->transport, 'test'))->storageSnapshots();
});

it('creates a storage snapshot', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', []])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'name' => 'full-2025.snapshot', 'creation_time' => '2025-01-01T00:00:00', 'size' => 2048,
        ]]));

    $snap = $this->snapshots->create();

    expect($snap)->toBeInstanceOf(SnapshotDescription::class)
        ->and($snap->name)->toBe('full-2025.snapshot');
});

it('throws a SnapshotException when storage snapshot creation fails', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', []])
        ->andReturn(new Response(['status' => ['error' => 'boom'], 'time' => 0.0]));

    expect(fn () => $this->snapshots->create())->toThrow(SnapshotException::class);
});

it('lists storage snapshots', function () {
    $this->transport->shouldReceive('get')
        ->withArgs([''])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            ['name' => 'full.snapshot', 'creation_time' => null, 'size' => 5],
        ]]));

    expect($this->snapshots->list())->toHaveCount(1);
});
