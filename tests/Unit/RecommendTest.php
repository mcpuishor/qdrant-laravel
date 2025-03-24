<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;
use Mcpuishor\QdrantLaravel\Enums\AverageVectorStrategy;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Query\Recommend;

beforeEach(function () {
    $this->collection = 'test';

    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->transport->shouldReceive('baseUri')
        ->passthru()
        ->andReturnSelf();
    $this->transport->shouldReceive('put', 'post', 'delete', 'get')->passthru();
    $this->searchEndpoint = "/collections/{$this->collection}/points/query";

    $this->query = new QdrantClient($this->transport, $this->collection);
});


it('can instantiate a recommend search', function () {
    $recommend = $this->query->recommend();

    expect($recommend)->toBeInstanceOf(Recommend::class);
});

it('can perform a basic recommend search with positives', function () {
    $positives = '123';

    $this->transport->shouldReceive('request')
        ->once()
        ->withArgs([
            'POST',
            $this->searchEndpoint,
            [
               "json" => [
                   "query" => [
                       'recommend' => [
                           'positive' => [$positives],
                           'strategy' => AverageVectorStrategy::AVERAGEVECTOR->value,
                       ],
                   ],
                   "params" => [
                       "hnsw_ef" => 128,
                       "exact" => false,
                   ],
                   "limit" => 10,
               ]
            ]
        ])
    ->andReturn(new Response([
            "result"=> [
                [ "id"=> 10, "score"=> 0.81 ],
                [ "id"=> 14, "score"=> 0.75 ],
                [ "id"=> 11, "score"=> 0.73 ]
              ],
              "status"=> "ok",
              "time"=> 0.001
        ]));

    $recommend = $this->query->recommend()
        ->positive($positives)
        ->get();

    expect($recommend)->toBeArray()->toHaveCount(3);
});

it('can perform a basic recommend search with negatives', function () {
    $negatives = 'a8b57c3e-4d19-4519-9c0b-c71956ba0506';

    $this->transport->shouldReceive('request')
        ->once()
        ->withArgs([
            'POST',
            $this->searchEndpoint,
            [
                "json" => [
                    "query" => [
                        'recommend' => [
                            'negative' => [$negatives],
                            'strategy' => AverageVectorStrategy::AVERAGEVECTOR->value,
                        ],
                    ],
                    "params" => [
                        "hnsw_ef" => 128,
                        "exact" => false,
                    ],
                    "limit" => 10,
                ]
            ]
        ])
        ->andReturn(new Response([
            "result"=> [
                [ "id"=> 10, "score"=> 0.81 ],
                [ "id"=> 14, "score"=> 0.75 ],
                [ "id"=> 11, "score"=> 0.73 ]
            ],
            "status"=> "ok",
            "time"=> 0.001
        ]));

    $recommend = $this->query->recommend()
        ->negative($negatives)
        ->get();

    expect($recommend)->toBeArray()->toHaveCount(3);
});

it('can perform a basic recommend search with a different strategy', function () {
    $positives = '123';
    $strategy = AverageVectorStrategy::BESTSCORE;
    $this->transport->shouldReceive('request')
        ->once()
        ->withArgs([
            'POST',
            $this->searchEndpoint,
            [
                "json" => [
                    "query" => [
                        'recommend' => [
                            'positive' => [$positives],
                            'strategy' => $strategy->value,
                        ]
                    ],
                    "params" => [
                        "hnsw_ef" => 128,
                        "exact" => false,
                    ],
                    "limit" => 10,
                ]
            ]
        ])
        ->andReturn(new Response([
            "result"=> [
                [ "id"=> 10, "score"=> 0.81 ],
                [ "id"=> 14, "score"=> 0.75 ],
                [ "id"=> 11, "score"=> 0.73 ]
            ],
            "status"=> "ok",
            "time"=> 0.001
        ]));
    $recommend = $this->query->recommend()
        ->positive($positives)
        ->strategy($strategy)
        ->get();

    expect($recommend)->toBeArray()->toHaveCount(3);
});
