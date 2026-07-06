<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
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
