<?php

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\QdrantSchema;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);

    $this->transport
        ->shouldReceive('baseUri', 'put', 'post', 'delete', 'get', 'patch')
        ->passthru();

    $this->qdrantSchema = new QdrantSchema(transport: $this->transport);
});

describe('Listing', function(){
    it('can list all collections', function () {
        $this->transport->shouldReceive('request')
            ->withArgs(['GET', '/collections'])
            ->andReturn(
                new Response ([
                    "result" => [
                        "collections" => [
                            ['name' => 'test'],
                            ['name' => 'test2'],
                            ['name' => 'test3'],
                        ]
                    ]
                ]));

        $response = $this->qdrantSchema->collections();

        expect($response)
            ->tobeInstanceOf(Collection::class)
            ->toHaveCount(3);
    });

    it('can check if a collection exists', function(){
        $collection = 'testcollection';
        $this->transport->shouldReceive('request')
            ->withArgs([
                'GET',
                "/collections/$collection/exists"
            ])
            ->andReturn(
                new Response(
                    [
                        'time' => 0.002,
                        'status' => 'ok',
                        'result' => [
                            'exists' => true,
                        ]
                    ]
                )
            );

        $result = $this->qdrantSchema->exists($collection);

        expect($result)->toBeTrue();
    });
});

describe('Collections', function() {
    it('can create a new collection in single mode', function () {
        $testCollectionName = 'testcollection';
        $this->transport->shouldReceive('request')
            ->withArgs([
                'PUT',
                '/collections/' . $testCollectionName,
                ['size' => 1000, 'distance' => 'cosine']
            ])
            ->andReturn(
                new Response([
                    'time' => 1,
                    'status' => 'ok',
                    'result' => true,
                ])
            );

        $response = $this->qdrantSchema
            ->create(
                $testCollectionName,
                ['size' => 1000, 'distance' => 'cosine']
            );

        expect($response)->toBeTrue();

    });

    it('can create a new collection in multiple vectors mode', function () {
        $testCollectionName = 'testcollection';
        $this->transport->shouldReceive('request')
            ->withArgs([
                'PUT',
                '/collections/' . $testCollectionName,
                [
                    'vector1' => ['size' => 1000, 'distance' => 'cosine'],
                    'vector2' => ['size' => 1000, 'distance' => 'cosine'],
                ]
            ])
            ->andReturn(
                new Response(
                    [
                        'time' => 1,
                        'status' => 'ok',
                        'result' => true,
                    ]
                )
            );

        $response = $this->qdrantSchema
            ->create(
                $testCollectionName,
                [
                    'vector1' => ['size' => 1000, 'distance' => 'cosine'],
                    'vector2' => ['size' => 1000, 'distance' => 'cosine'],
                ]
            );

        expect($response)->toBeTrue();
    });

    it('can update a collection', function() {
        $collectionName = 'test';
        $this->transport->shouldReceive('request')
            ->withArgs([
                'PATCH',
                "/collections/$collectionName",
                ['size' => 1000, 'distance' => 'cosine']
            ])
            ->andReturn(
               new Response([
                   'time' => 1,
                   'status' => 'ok',
                   'result' => true,
               ])
            );

        $result = $this->qdrantSchema->update($collectionName, ['size' => 1000, 'distance' => 'cosine']);

        expect($result)->toBeTrue();
    });

    it('throws an error if it cannot create a collection', function () {
        $collectionName = 'test';
        $this->transport->shouldReceive('request')
            ->withArgs([
                'PUT',
                "/collections/$collectionName",
                ['size' => 1000, 'distance' => 'cosine']
            ])
            ->andThrow(new FailedToCreateCollectionException('Cannot create collection'));

        $result = $this->qdrantSchema->create($collectionName, ['size' => 1000, 'distance' => 'cosine']);

    })->throws(FailedToCreateCollectionException::class, 'Cannot create collection');

    it('can delete a collection', function () {
        $collectionName = 'test';
        $this->transport->shouldReceive('request')
            ->withArgs([
                "DELETE",
                "/collections/$collectionName"
            ])
            ->andReturn(
                new Response(
                    [
                        'time' => 1,
                        'status' => 'ok',
                        'result' => true
                    ]
                )
            );
        $result = $this->qdrantSchema->delete($collectionName);

        expect($result)->toBeTrue();
    });

    it('cannot delete a non-existent collection', function () {
        $collectionName = 'test2';
        $this->transport->shouldReceive('request')
            ->withArgs([
                "DELETE",
                "/collections/$collectionName"
            ])
            ->andReturn(
                new Response(
                    [
                        'time' => 1,
                        'status' => 'ok',
                        'result' => false
                    ]
                )
            );
        $result = $this->qdrantSchema->delete($collectionName);

        expect($result)->toBeFalse();
    });
});

describe('Failing tests', function() {
    beforeEach(function () {
        $this->transport->shouldReceive('request')
            ->withSomeOfArgs([
                'PUT',
                '/collections/test',
            ]);
    });

    it('throws an exception if the distance parameter is invalid', function () {
        $this->qdrantSchema
            ->create('test', [
                'size' => 1000,
                'distance' => 1 //invalid option
            ]);

    })->throws(\InvalidArgumentException::class, 'Invalid distance metric: ');

    it('throws an exception if the size parameter is invalid', function () {
        $this->qdrantSchema
            ->create('test', [
                'size' => 0, //invalid option
                'distance' => 'cosine'
            ]);

    })->throws(\InvalidArgumentException::class, 'Invalid size metric: ');
});
