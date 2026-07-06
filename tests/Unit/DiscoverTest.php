<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('discovers points with a target and context', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['', [
            'target' => 5,
            'context' => [['positive' => 1, 'negative' => 2]],
            'limit' => 10,
        ]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            ['id' => 7, 'score' => 0.9],
        ]]));

    $result = $this->client->discover()
        ->target(5)
        ->context([['positive' => 1, 'negative' => 2]])
        ->limit(10)
        ->get();

    expect($result)->toHaveCount(1);
});

it('throws a SearchException when getting without a target or context', function () {
    expect(fn () => $this->client->discover()->get())->toThrow(SearchException::class);
});

it('submits a discover batch with the payloads of each discover instance', function () {
    $this->transport->shouldReceive('post')->once()
        ->withArgs(['/batch', [
            'searches' => [
                ['target' => 5, 'limit' => 10],
                ['target' => 9, 'limit' => 10],
            ],
        ]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => []]));

    $first = $this->client->discover()->target(5);
    $second = (new QdrantClient($this->transport, 'test'))->discover()->target(9);

    $result = $first->batch([$first, $second]);

    expect($result)->toBe([]);
});
