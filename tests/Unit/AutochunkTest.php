<?php

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Query\Autochunk;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();

    $this->transport->shouldReceive('put', 'get', 'post', 'delete')
        ->andReturn(new \Mcpuishor\QdrantLaravel\DTOs\Response([]));

    $this->query = new QdrantClient($this->transport, 'test');
    $this->autochunk = $this->query->collection('test')->points()->autochunk();
});

it('can instantiate a Autochunk from Points', function () {
    expect($this->autochunk)->toBeInstanceOf(Autochunk::class)
        ->and($this->autochunk->count())->toBe(0);
});

it('can add points to the Autochunk', function () {
    $this->autochunk->add(
        new Point(
            id: 1,
            vector: [1, 1, 1, 1],
            payload: [
                'attr1' => 'value1',
            ]
        )
    );

    expect($this->autochunk)->toBeInstanceOf(Autochunk::class)
        ->and($this->autochunk->count())->toBe(1);
});

it('auto-flushes when auto-chunking is enabled', function (int $chunkSize, int $totalPoints, int $leftoverPoints) {
    $this->autochunk = $this->query->collection('test')->points()->autochunk($chunkSize);

    for($i = 0; $i < $totalPoints; $i++) {
        $this->autochunk->add(
            new Point(
                id: $i,
                vector: [mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100)],
                payload: ['value' => 'test']
            )
        );
    }
    expect($this->autochunk->count())
        ->toBe($leftoverPoints);
})->with([
    'exact' => [ 10, 20, 10 ],
    'left1' => [ 5, 16, 1 ],
]);
