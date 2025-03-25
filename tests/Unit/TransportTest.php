<?php

use Mcpuishor\QdrantLaravel\QdrantTransport;

it('can instantiate the default connection', function () {
    $transport = app()->make(QdrantTransport::class);

    expect($transport)->toBeInstanceOf(QdrantTransport::class);
});

it('can instantiate a custom connection', function () {
    $client = app()->make(QdrantTransport::class, ['connection' => 'qdrant']);

    expect($client)->toBeInstanceOf(QdrantTransport::class);
});

it('ensures that the connection is a singleton', function () {
    $transport1 = app()->make(QdrantTransport::class);
    $transport2 = app()->make(QdrantTransport::class);

    expect($transport1)->toBe($transport2)
        ->and($transport1)->toBeInstanceOf(QdrantTransport::class)
        ->and($transport2)->toBeInstanceOf(QdrantTransport::class);
});
