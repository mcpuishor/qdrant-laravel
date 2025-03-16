<?php

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Facades\Client;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\QdrantClient;

beforeEach(function () {
    $this->qdrantClient = Mockery::mock(QdrantTransport::class);
});

it('can get an instance of the Client class', function () {
    $builder = Client::collection('test');

    expect($builder)->toBeInstanceOf(class: QdrantClient::class);
});

describe('Retrieval', function () {

    it('can retrieve list of points by ID', function () {
        $testCollectionName = 'test';

       $this->qdrantClient->shouldReceive('request')
           ->withArgs([
               'POST',
               "/collections/$testCollectionName/points",
               ['json' => ['ids' => [1, 2], 'with_payload' => true, 'with_vector' => true]],
           ])
            ->andReturn(new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    [
                        'id' => 1,
                        'payload' => ['test', 'payload info'],
                        'vector' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                        'shard_key' => "region:1",
                        "order_value" => 90
                    ],
                ]
            ]));

       $result = (new QdrantClient($this->qdrantClient, $testCollectionName))
                    ->points()->withPayload()->withVector()
                    ->get(ids: [1, 2]);

       expect($result)->toBeCollection()
           ->toHaveCount(1);
    });

    it('can retrieve a single point', function () {
        $testCollectionName = 'test';
        $testId = 1;
        $this->qdrantClient->shouldReceive('request')
            ->withArgs([
                'GET',
                "/collections/$testCollectionName/points/$testId",
            ])
            ->andReturn(new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    [
                        'id' => 1,
                        'payload' => ['test', 'payload info'],
                        'vector' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
//                        'shard_key' => "region_1",
//                        "order_value" => 90
                    ],
                ]
            ]));

        $result = (new QdrantClient($this->qdrantClient, $testCollectionName))
                    ->points()->withoutPayload()->withoutVector()
                    ->get($testId);

        expect($result)
            ->toBeInstanceOf(\Mcpuishor\QdrantLaravel\DTOs\Point::class)
            ->toHaveProperty('id', 1)
            ->toHaveProperty('vector', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

    });
});

describe('Points creation', function () {
    beforeEach(function () {
        $this->testCollectionName = 'test';
        $this->query = (new QdrantClient($this->qdrantClient, $this->testCollectionName));
    });

    it('can upsert points', function () {
        $testId = 1;

        $points = collect([
            new Point(
                id: $testId,
                vector: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                payload: ['test', 'payload info'],
            ),
            new Point(
                id: $testId,
                vector: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                payload: ['test', 'payload info'],
            ),
        ]);

        $this->qdrantClient->shouldReceive('request')
            ->withArgs([
                'PUT',
                "/collections/{$this->testCollectionName}/points",
                ['json' => ['points' => $points->toArray()]]
            ])
            ->andReturn(new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    'status' => 'acknowledged',
                    'operation_id' => 1,
                ]
            ]));

        $result = $this->query->points()->upsert($points);

        expect($result)->toBetrue();
    });
});

describe('Points deletion', function () {
    it('can delete points by ID', function () {
        $this->qdrantClient->shouldReceive('request')
            ->withArgs([
                'POST',
                '/collections/test/points/delete',
                ['json' => ['points' => [1, 2]]]
            ])
            ->andReturn( new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    "status" => "acknowledged",
                    "operation_id" => 1
                ],
            ]));

        $result = (new QdrantClient($this->qdrantClient, 'test'))
            ->points()
            ->delete([1,2]);

        expect($result)->toBeTrue();
    });
});
