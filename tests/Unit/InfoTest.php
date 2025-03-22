<?php

use Mcpuishor\QdrantLaravel\DTOs\Collection\Info;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;


it('can retrieve the information about a collection', function ($infoResult) {
    $collection = 'test';
    $transport = Mockery::mock(QdrantTransport::class);

    $transport
        ->shouldReceive('baseUri', 'put', 'post', 'delete', 'get', 'patch')
        ->passthru();

    $transport->shouldReceive('request')
        ->once()
        ->withArgs(['GET', '/collections/' . $collection])
        ->andReturn(new Response($infoResult));

    $client = new QdrantClient($transport,  $collection);

    $result = $client->info()->get();

    expect($result)->toBeInstanceOf(Info::class);
})->with('collectionInfo');
