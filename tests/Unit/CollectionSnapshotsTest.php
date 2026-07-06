<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\DTOs\SnapshotDescription;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->snapshots = (new QdrantClient($this->transport, 'test'))->snapshots();
});

it('creates a collection snapshot', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', []])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'name' => 'test-2025.snapshot', 'creation_time' => '2025-01-01T00:00:00', 'size' => 1024,
        ]]));

    $snap = $this->snapshots->create();

    expect($snap)->toBeInstanceOf(SnapshotDescription::class)
        ->and($snap->name)->toBe('test-2025.snapshot');
});

it('lists collection snapshots', function () {
    $this->transport->shouldReceive('get')
        ->withArgs([''])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            ['name' => 'a.snapshot', 'creation_time' => null, 'size' => 1],
        ]]));

    expect($this->snapshots->list())->toHaveCount(1);
});
