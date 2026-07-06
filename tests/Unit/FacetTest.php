<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('facets a payload key', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['/facet', ['key' => 'color', 'limit' => 5, 'exact' => false]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'hits' => [['value' => 'red', 'count' => 3], ['value' => 'blue', 'count' => 1]],
        ]]));

    $facet = $this->client->facet('color')->limit(5)->get();

    expect($facet->hits())->toHaveCount(2)
        ->and($facet->hits()[0])->toBe(['value' => 'red', 'count' => 3]);
});
