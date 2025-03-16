<?php

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->testCollectionName = 'test';
    $this->query = new QdrantClient($this->transport, $this->testCollectionName);
});

describe('Payloads update', function () {
    it('can set the payload of a point', function (PointsCollection $pointsCollection) {
        $updatedPayload = [
            'property1' => 'test',
            'property2' => 'test2',
        ];

        $this->transport->shouldReceive('request')
            ->withArgs([
                'POST',
                "/collections/{$this->testCollectionName}/points/payload",
                ['json' => [
                    'points' => $pointsCollection->toArray(),
                    'payload' => $updatedPayload,
                    ]
                ]
            ])
            ->andReturn( new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    "status" => "acknowledged",
                    "operation_id" => 1
                ],
            ]));

        $result = $this->query->payloads()
            ->for( $pointsCollection->toArray() )
            ->set($updatedPayload);

        expect($result)->toBeTrue();

    })->with('points');
});

describe('Payloads clearing', function () {
    it('can clear the payload keys for points by ID', function (PointsCollection $pointsCollection) {
        $keysToDelete = ['property1', 'property2',];
        $this->transport->shouldReceive('request')
            ->withArgs([
                'POST',
                "/collections/{$this->testCollectionName}/points/payload/delete",
                ['json' => [
                    'points' => $pointsCollection->toArray(),
                    'keys' => $keysToDelete,
                    ]
                ]
            ])
            ->andReturn( new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    "status" => "acknowledged",
                    "operation_id" => 1
                ],
            ]));


        $result = $this->query->payloads()
            ->for( $pointsCollection->toArray() )
            ->clear($keysToDelete);

        expect($result)->toBeTrue();

    })->with('points');

    it('can clear all keys for points by ID', function (PointsCollection $pointsCollection) {
        $keysToDelete = ['property1', 'property2',];
        $this->transport->shouldReceive('request')
            ->withArgs([
                'POST',
                "/collections/{$this->testCollectionName}/points/payload/clear",
                ['json' => [
                    'points' => $pointsCollection->toArray(),
                    ]
                ]
            ])
            ->andReturn( new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    "status" => "acknowledged",
                    "operation_id" => 1
                ],
            ]));


        $result = $this->query->payloads()
            ->for( $pointsCollection->toArray() )
            ->clearAll();

        expect($result)->toBeTrue();

    })->with('points');
});
