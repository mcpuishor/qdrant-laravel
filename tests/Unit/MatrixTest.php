<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('requests a distance matrix as offsets', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['/offsets', ['sample' => 10, 'limit' => 3]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'offsets_row' => [0, 0], 'offsets_col' => [1, 2], 'scores' => [0.9, 0.8], 'ids' => [1, 2, 3],
        ]]));

    $result = $this->client->matrix()->sample(10)->limit(3)->offsets();

    expect($result)->toHaveKey('scores');
});
