<?php

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('submits a batch of mixed operations', function () {
    $points = PointsCollection::make([new Point(id: 1, vector: [0.1, 0.2])]);

    $this->transport->shouldReceive('post')
        ->withArgs(['/batch', [
            'operations' => [
                ['upsert' => ['points' => $points->toArray()]],
                ['delete' => ['points' => [2, 3]]],
            ],
        ]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => []]));

    $ok = $this->client->batch()
        ->upsert($points)
        ->deletePoints([2, 3])
        ->execute();

    expect($ok)->toBeTrue();
});

it('submits a deleteVectors batch operation', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['/batch', [
            'operations' => [
                ['delete_vectors' => ['points' => [1, 2], 'vector' => ['image']]],
            ],
        ]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => []]));

    $ok = $this->client->batch()
        ->deleteVectors([1, 2], ['image'])
        ->execute();

    expect($ok)->toBeTrue();
});
