<?php

use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;


beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
});

describe('Vector', function () {
    beforeEach(function () {
        $this->testCollectionName = 'test';
        $this->query = new QdrantClient($this->transport, $this->testCollectionName);
    });

    it('can update vectors', function (PointsCollection $pointsCollection) {
        $this->transport->shouldReceive('request')
            ->withArgs([
                'PUT',
                "/collections/{$this->testCollectionName}/points/vectors",
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

        $result = $this->query->vectors()->update($pointsCollection);

        expect($result)->toBeTrue();
    })->with('points');

    it('can delete vectors by id', function () {
        $vectorsToDelete = [1, 3, 10];

        $this->transport->shouldReceive('request')
            ->withArgs([
                'POST',
                "/collections/{$this->testCollectionName}/points/vectors/delete",
                [
                    'json' => [
                        'points' => $vectorsToDelete,
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

        $result= $this->query->vectors()->delete($vectorsToDelete);

        expect($result)->toBeTrue();
    });
});
