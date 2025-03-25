<?php

use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);

    $this->transport->shouldReceive('baseUri', 'getBaseUri')
        ->passthru()
        ->andReturnSelf();

    $this->query = new QdrantClient($this->transport);
});

it('can retrieve all aliases', function () {
    $this->transport->shouldReceive('get')->once()
        ->withArgs([
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

    $result = $this->query->aliases()->get();

    expect($result)->toBeCollection()
        ->and($this->transport->getBaseUri())->toBe("");
});

it('can retrieve aliases for a collection', function () {
    $testCollection = 'test';
    $this->transport->shouldReceive('get')->once()
        ->withArgs([
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

    $result = $this->query->collection($testCollection)->aliases()->get();

    expect($result)->toBeCollection()
        ->and($this->transport->getBaseUri())->toBe("/collections/$testCollection");
});
