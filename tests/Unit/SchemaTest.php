<?php

use Illuminate\Support\Collection;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\DTOs\Collection\OptimizersConfig;
use Mcpuishor\QdrantLaravel\DTOs\Vector;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Schema\Schema;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);

    $this->transport
        ->shouldReceive('baseUri')
        ->passthru();

    Http::fake();

    $this->qdrantSchema = new Schema(transport: $this->transport);
});

describe('Listing', function(){
    it('can list all collections', function () {
        $this->transport->shouldReceive('get')
            ->withArgs([''])
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
        $this->transport->shouldReceive('get')
            ->withArgs([
                "/$collection/exists"
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

    it('returns false for a non existing collection', function(){
        $collection = 'testcollection';
        $this->transport->shouldReceive('get')
            ->withArgs([
                "/$collection/exists"
            ])
            ->andReturn(
                new Response(
                    [
                        'time' => 0.002,
                        'status' => 'ok',
                        'result' => [
                            'exists' => false,
                        ]
                    ]
                )
            );

        $result = $this->qdrantSchema->exists($collection);

        expect($result)->toBeFalse();
    });

    it('throws an error if the response is not valid', function(){
        $collection = 'testcollection';
        $this->transport->shouldReceive('get')
            ->withArgs([
                "/$collection/exists"
            ])
            ->andReturn(
                new Response(
                    [
                        'time' => 0.002,
                        'status' => 'ok',
                        'result' => [
                            'status' => 'error',
                        ]
                    ]
                )
            );
        $this->qdrantSchema->exists($collection);
    })->throws(InvalidArgumentException::class, 'Error in response from Qdrant server.');

});

describe('Collections', function() {
    it('can create a new collection in single mode', closure: function () {
        $testCollectionName = 'testcollection';
        $this->transport->shouldReceive('put')
            ->withArgs([
                 "/". $testCollectionName,
                [
                    "vectors" => [
                        'size' => 1000,
                        'distance' => 'Cosine'
                    ]
                ]
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
                ['size' => 1000, 'distance' => 'Cosine']
            );

        expect($response)->toBeTrue();

    });

    it('can create a new collection in multiple vectors mode', function () {
        $testCollectionName = 'testcollection';
        $this->transport->shouldReceive('put')
            ->withArgs([
                '/' . $testCollectionName,
                [
                    "vectors" => [
                        'vector1' => ['size' => 1000, 'distance' => DistanceMetric::COSINE->value],
                        'vector2' => ['size' => 1000, 'distance' => DistanceMetric::DOT->value],
                    ]
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
                    'vector1' => ['size' => 1000, 'distance' => DistanceMetric::COSINE->value],
                    'vector2' => ['size' => 1000, 'distance' => DistanceMetric::DOT->value],
                ]
            );

        expect($response)->toBeTrue();
    });

    it('can update the parameters of a collection', function(){
        $collectionName = "test";
        $optionsUpdate = [
            OptimizersConfig::fromArray([
                'replication_factor' => 2
            ])
        ];

        $this->transport->shouldReceive('patch')
            ->withArgs([
                "/$collectionName",
                $optionsUpdate,
            ])
            ->andReturn(
               new Response([
                   'time' => 1,
                   'status' => 'ok',
                   'result' => true,
               ])
            );

        $result = $this->qdrantSchema
                ->update(
                   collectionName: $collectionName, 
                    options: $optionsUpdate
                );

        expect($result)->toBeTrue();
    });

    it('can update the vectors of a collection', function() {
        $collectionName = 'test';

        $vectorsUpdate = [
                "vector1" => [
                    'on_disk' => true,
                ],
        ];

        $this->transport->shouldReceive('patch')
            ->withArgs([
                "/$collectionName",
                [
                    'vectors' => $vectorsUpdate,
                ],
            ])
            ->andReturn(
               new Response([
                   'time' => 1,
                   'status' => 'ok',
                   'result' => true,
               ])
            );

        $result = $this->qdrantSchema
                ->update(
                   collectionName: $collectionName, 
                    vectors: $vectorsUpdate
                );

        expect($result)->toBeTrue();
    });

    it('throws an error if it cannot create a collection', function () {
        $collectionName = 'test';
        $this->transport->shouldReceive('put')
            ->withArgs([
                "/$collectionName",
                ["vectors" => ['size' => 1000, 'distance' => DistanceMetric::COSINE->value]],
            ])
            ->andThrow(new FailedToCreateCollectionException('Cannot create collection'));

        $this->qdrantSchema->create($collectionName, ['size' => 1000, 'distance' => DistanceMetric::COSINE->value]);

    })->throws(FailedToCreateCollectionException::class, 'Cannot create collection');

    it('can delete a collection', function () {
        $collectionName = 'test';
        $this->transport->shouldReceive('delete')
            ->withArgs([
                "/$collectionName"
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
        $this->transport->shouldReceive('delete')
            ->withArgs([
                "/$collectionName"
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
        $this->transport->shouldReceive('put')
            ->withSomeOfArgs([
                '/test',
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
                'distance' => 'Cosine'
            ]);

    })->throws(\InvalidArgumentException::class, 'Invalid size metric: ');
});