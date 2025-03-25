<?php

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Facades\Client;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\QdrantClient;

beforeEach(function () {
    $this->testCollectionName = 'test';
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')
        ->passthru()
        ->andReturnSelf();

    $this->pointsQuery =  (new QdrantClient($this->transport, $this->testCollectionName))
        ->points();

});

it('can get an instance of the Client class', function () {
    $builder = Client::collection('test');

    expect($builder)->toBeInstanceOf(class: QdrantClient::class);
});

describe('Retrieval', function () {

    it('can retrieve list of points by ID', function () {

       $this->transport->shouldReceive('post')
           ->withArgs([
               "",
               ['ids' => [1, 2], 'with_payload' => true, 'with_vector' => true],
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

       $result = $this->pointsQuery->withPayload()->withVector()
                    ->get(ids: [1, 2]);

       expect($result)->toBeCollection()
           ->toHaveCount(1);
    });

    it('can retrieve a single point', function () {
        $testId = 1;
        $this->transport->shouldReceive('get')
            ->withArgs([
                "/$testId",
            ])
            ->andReturn(new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    'id' => 1,
                    'payload' => ['test', 'payload info'],
                    'vector' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                ]
            ]));

        $result = $this->pointsQuery->withoutPayload()->withoutVector()
                    ->find($testId);

        expect($result)
            ->toBeInstanceOf(Point::class)
            ->toHaveProperty('id', 1)
            ->toHaveProperty('vector', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

    });
});

describe('Points creation', function () {
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

        $this->transport->shouldReceive('put')
            ->withArgs([
                "",
                ['points' => $points->toArray()]
            ])
            ->andReturn(new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    'status' => 'acknowledged',
                    'operation_id' => 1,
                ]
            ]));

        $result = $this->pointsQuery->upsert($points);

        expect($result)->toBetrue();
    });
});

describe('Points deletion', function () {
    it('can delete points by ID', function () {
        $this->transport->shouldReceive('post')
            ->withArgs([
                '/delete',
                ['points' => [1, 2]]
            ])
            ->andReturn( new Response([
                'time' => 1,
                'status' => 'ok',
                'result' => [
                    "status" => "acknowledged",
                    "operation_id" => 1
                ],
            ]));

        $result =$this->pointsQuery->delete([1,2]);

        expect($result)->toBeTrue();
    });
});
