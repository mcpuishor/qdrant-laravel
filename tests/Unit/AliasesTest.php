<?php

use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);

    $this->transport->shouldReceive('baseUri', 'getBaseUri')
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

    expect($result)->toBeCollection();
    $this->transport->shouldHaveReceived('baseUri')->with("");
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

    expect($result)->toBeCollection();
    $this->transport->shouldHaveReceived('baseUri')->with("/collections/$testCollection");
});

it('applies alias actions to the correct endpoint with the correct payload', function () {
    $this->transport->shouldReceive('post')->once()
        ->withArgs([
            "",
            [
                "actions" => [
                    [
                        "create_alias" => [
                            "collection_name" => "coll1",
                            "alias_name" => "alias1",
                        ],
                    ],
                ],
            ],
        ])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    $result = $this->query->aliases()->add('alias1', 'coll1')->apply();

    expect($result)->toBeTrue();
    $this->transport->shouldHaveReceived('baseUri')->with("/collections/aliases");
});
