<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('creates a named vector', function () {
    $this->transport->shouldReceive('put')
        ->withArgs(['/image', ['size' => 512, 'distance' => 'Cosine']])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    $ok = $this->client->namedVectors()->create('image', ['size' => 512, 'distance' => DistanceMetric::COSINE->value]);

    expect($ok)->toBeTrue();
});

it('deletes a named vector', function () {
    $this->transport->shouldReceive('delete')
        ->withArgs(['/image'])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => true]));

    expect($this->client->namedVectors()->delete('image'))->toBeTrue();
});
