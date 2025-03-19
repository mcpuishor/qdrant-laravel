<?php

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Enums\FilterVerbs;
use Mcpuishor\QdrantLaravel\Exceptions\SearchException;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\Query\Search;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function () {
    $this->testCollectionName = 'test';
    $this->fieldName = 'field';
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->query = new QdrantClient($this->transport, $this->testCollectionName);

    $this->searchEndpoint = "/collections/{$this->testCollectionName}/points/query";

    $this->vector = [1, 2, 3];

    $this->validResponse = new Response([
        "result" => [
            ["id" => 10, "score" => 0.81],
            ["id" => 14, "score" => 0.75],
            ["id" => 11, "score" => 0.73],
        ],
        "status" => "ok",
        "time" => 1
    ]);
});

it('creates an instance of Query class', function () {
    $result = $this->query->search();

    expect($result)->toBeInstanceOf(Search::class);
});

it('can perform a simple search by vector', function (){
    $this->transport->shouldReceive('request')
        ->withArgs([
            'POST',
            $this->searchEndpoint,
            ['json' => [
                'query' => $this->vector,
                "params" => [
                    "hnsw_ef" => 128,
                    "exact" => false,
                ],
                "limit" => 10,
            ]]
        ])
        ->andReturn($this->validResponse);


    $result = $this->query->search()->vector($this->vector);

    expect($result)->toBeArray()
        ->toHaveCount(3);
});

it('throws an exception if the search cannot be performed', function () {
    $this->transport->shouldReceive('request')
        ->withArgs([
            'POST',
            $this->searchEndpoint,
            ['json' => [
                'query' => $this->vector,
                "params" => [
                    "hnsw_ef" => 128,
                    "exact" => false,
                ],
                "limit" => 10,
            ]]
        ])
        ->andReturn(new Response([
            'status' => 'error',
            'message' => 'Something went wrong.'
        ]));

    $this->query->search()->vector($this->vector);

})->throws(SearchException::class);

it('can add a filter to the search query', function (string $term, FilterConditions $condition, string $value) {

    $this->transport->shouldReceive('request')
        ->once()
        ->withArgs([
            "POST",
            $this->searchEndpoint,
            ['json' => [
                "query" => $this->vector,
                "params" => [
                    "hnsw_ef" => 128,
                    "exact" => false,
                ],
                "limit" => 10,
                "filter" => [
                   FilterVerbs::MUST->value => [
                       [
                            "key" => $term,
                            $condition->value => [
                                'value' => $value
                            ],
                       ],
                    ]
                ],
            ]]
        ])->andReturn($this->validResponse);


    $result = $this->query->search()
                ->must(
                    $term,
                    $condition,
                    $value
                )
                ->vector($this->vector);

    expect($result)
        ->toBeArray()
        ->toHaveCount(3);
})->with([
    "dataset1" => [ 'field1', FilterConditions::MATCH, 'value1' ],
    "dataset3" => [ 'field3', FilterConditions::RANGE, 'value1'],
    "dataset5" => [ 'field5', FilterConditions::IS_EMPTY, '' ],
]);

it('can switch on the payload return', function () {

   $query = $this->query->search()->withPayload()->add($this->vector);

   expect($query->getSearchPayload())
       ->toBeArray()
       ->toHaveKey('with_payload', true);
});

it('can switch on the vectors return', function () {
   $query = $this->query->search()->withVectors()->add($this->vector);

   expect($query->getSearchPayload())
       ->toBeArray()
       ->toHaveKey('with_vectors', true);
});

it('can select the fields in payload to return', function (){
    $field = "test_field";
    $query = $this->query->search()->include([$field])->add($this->vector);

    expect($query->getSearchPayload())
        ->toBeArray()
        ->toHaveKey('with_payload')
    ->and($query->getSearchPayload()['with_payload'])
        ->toBeArray()
        ->toHaveKey('only', [$field]);
});

it('can exclude fields from the payload from the return', function(){
    $field = "test_field";
    $query = $this->query->search()->exclude([$field])->add($this->vector);

    expect($query->getSearchPayload())
        ->toBeArray()
        ->toHaveKey('with_payload')
        ->and($query->getSearchPayload()['with_payload'])
        ->toBeArray()
        ->toHaveKey('exclude', [$field]);
});

it('throws an exception if the vector is not provided', function ($vector) {
    $this->transport->shouldReceive('request')
        ->withAnyArgs()
        ->never();

    $this->query->search()->vector($vector);
})->with([
    "empty" => [ [] ] //the argument is an empty vector
])->throws(SearchException::class, 'Search vector cannot be empty.');

it('throws an exception if the point is empty', function ($vector) {
    $this->transport->shouldReceive('request')
        ->withAnyArgs()
        ->never();

    $this->query->search()->point(new Point( id: 1, vector: $vector ));
})->with([
    "empty" => [ [] ] //the argument is an empty vector
])->throws(SearchException::class, 'Search point cannot be empty.');

it('can restrict the number of results returned', function () {
    $newLimit = 3;
   $this->transport->shouldReceive('request')
       ->withArgs([
           'POST',
           $this->searchEndpoint,
           ['json' => [
               "query" => $this->vector,
               "params" => [
                   "hnsw_ef" => 128,
                   "exact" => false,
               ],
               "limit" => $newLimit,
           ]]
       ])->andReturn($this->validResponse);

   $result = $this->query->search()->limit($newLimit)->vector($this->vector);

   expect($result)->toBeArray()
       ->toHaveCount(3);
});

it('throws an exception if the limit is not a positive integer', function () {
    $this->transport->shouldReceive('request')
        ->withAnyArgs()
        ->never();

    $this->query->search()->limit(-1);
})->throws(SearchException::class, 'Limit must be greater than 0.');

it('can submit a batch of searches at once', function () {
    $this->transport->shouldReceive('request')
        ->withSomeOfArgs([
            'POST',
            $this->searchEndpoint,
        ]);

    $result = $this->query->search()->batch([
        $this->query->search()->must('key1', FilterConditions::MATCH, 'test1' )->add($this->vector),
        $this->query->search()->limit(5)->add($this->vector),
        $this->query->search()->withPayload()->withVectors()->add($this->vector),
        $this->query->search()->include(['test1', 'city'])->withVectors()->add($this->vector),
        $this->query->search()->exclude(['test1', 'city'])->withVectors()->add($this->vector),
    ]);
});
