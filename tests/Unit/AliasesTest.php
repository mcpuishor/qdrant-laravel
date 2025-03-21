<?php

use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')
        ->passthru()
        ->andReturnSelf();
    $this->transport->shouldReceive('put', 'post', 'delete', 'get')->passthru();
    $this->query = new QdrantClient($this->transport, 'test');
});

it('can retrieve all aliases', function () {
    $this->transport->shouldReceive('request')->once()
        ->withArgs([
            'GET',
            '/aliases',
        ])
        ->andReturn(new Response([
            "result" => [
                [
                    "alias_name" => "test123",
                    "collection_name" => "test21",
                ],
                [
                    "alias_name" => "collection_alias",
                    "collection_name" => "test",
                ],
            ]
        ]));

    $result = $this->query->aliases()->all();

    expect($result)->toBeCollection();
});

it('can retrieve aliases for a collection', function () {
    $testCollection = 'test';
    $this->transport->shouldReceive('request')->once()
        ->withArgs([
            'GET',
            '/collections/'. $testCollection . '/aliases',
        ])
        ->andReturn(new Response([
            "result" => [
                [
                    "alias_name" => "test123",
                    "collection_name" => "test21",
                ],
                [
                    "alias_name" => "collection_alias",
                    "collection_name" => "test",
                ],
            ]
        ]));

    $result = $this->query->aliases()->all($testCollection);

    expect($result)->toBeCollection();
});
