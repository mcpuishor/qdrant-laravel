<?php

use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->client = new QdrantClient($this->transport, 'test');
});

it('counts points with a filter and exact flag', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(['/count', [
            'filter' => ['must' => [['key' => 'city', 'match' => ['value' => 'London']]]],
            'exact' => true,
        ]])
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => ['count' => 42]]));

    $count = $this->client->count()
        ->must('city', FilterConditions::MATCH, 'London')
        ->exact()
        ->get();

    expect($count)->toBe(42);
});
