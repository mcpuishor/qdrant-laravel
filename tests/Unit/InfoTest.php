<?php

use Mcpuishor\QdrantLaravel\DTOs\Collection\Info;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;


it('can retrieve the information about a collection', function ($infoResult) {
    $transport = Mockery::mock(QdrantTransport::class);
    $collection = 'test';

    $transport
        ->shouldReceive('baseUri')
        ->passthru();

    $transport->shouldReceive('get')
        ->once()
        ->withArgs(['/' . $collection])
        ->andReturn(new Response($infoResult));

    $client = new QdrantClient($transport,  $collection);

    $result = $client->info()->get();

    expect($result)->toBeInstanceOf(Info::class);
})->with('collectionInfo');
