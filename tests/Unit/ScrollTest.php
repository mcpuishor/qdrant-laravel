<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('scrolls with limit, order_by and returns points plus next offset', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['/scroll', [
            'limit' => 2,
            'with_payload' => true,
            'with_vector' => false,
            'order_by' => ['key' => 'created_at', 'direction' => 'desc'],
        ]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => [
            'points' => [['id' => 1, 'payload' => [], 'vector' => null]],
            'next_page_offset' => 10,
        ]]));

    $scroll = $this->client->scroll()->limit(2)->orderBy('created_at', 'desc');
    $points = $scroll->get();

    expect($points)->toHaveCount(1)
        ->and($scroll->nextPageOffset())->toBe(10);
});
