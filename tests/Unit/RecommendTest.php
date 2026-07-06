<?php
use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Enums\AverageVectorStrategy;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

beforeEach(function () {
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')->passthru()->andReturnSelf();
    $this->recommend = (new QdrantClient($this->transport, 'test'))->recommend();
});

it('builds a query-api recommend body', function () {
    $this->transport->shouldReceive('post')
        ->withArgs(function ($uri, $options) {
            return $uri === ''
                && $options['query']['recommend']['positive'] === [1, 2]
                && $options['query']['recommend']['negative'] === [3]
                && $options['query']['recommend']['strategy'] === AverageVectorStrategy::default()->value
                && $options['limit'] === 10;
        })
        ->andReturn(new Response(['status' => 'ok', 'time' => 0.0, 'result' => []]));

    $result = $this->recommend->positive([1, 2])->negative(3)->get();

    expect($result)->toBeInstanceOf(PointsCollection::class);
});
