<?php

use Mcpuishor\QdrantLaravel\Enums\FilterConditions;
use Mcpuishor\QdrantLaravel\Enums\FilterVerbs;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function(){
    $this->query = app()->make('qdrantclient')->collection('test')->search();

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

describe('Simple search filters', function(){

    it('can add a MUST filter', function () {
        $this->query
            ->must('someKey', FilterConditions::MATCH, 'someValue');

        expect($this->query->getFilters())
            ->toBeArray()
            ->and($this->query->getFilters()[FilterVerbs::MUST->value])
            ->toHaveCount(1);
    });

    it('can add two SHOULD filters', function () {
        $this->query
            ->should('someKey', FilterConditions::MATCH, 'someValue')
            ->should('someNew', FilterConditions::MATCH, 'someValue');

        expect($this->query->getFilters())
            ->toBeArray()
            ->toHaveKey(FilterVerbs::SHOULD->value)
            ->and($this->query->getFilters()[FilterVerbs::SHOULD->value])
            ->toHaveCount(2);
    });

    it('can add MUST NOT filters', function () {
        $this->query
            ->mustNot('someKey', FilterConditions::MATCH, 'someValue')
            ->mustNot('someNew', FilterConditions::MATCH, 'someValue2')
            ->mustNot('someNew2', FilterConditions::MATCH, 'someValue3')
        ;

        expect($this->query->getFilters())
            ->toBeArray()
            ->toHaveKey(FilterVerbs::MUST_NOT->value)
            ->and($this->query->getFilters()[FilterVerbs::MUST_NOT->value])
            ->toHaveCount(3);
    });

    it('can add a MIN SHOULD filter', function(){
        $this->query
            ->minShould('someKey', FilterConditions::MATCH, 'somevalue', 1)
            ->minShould('someotherkey', FilterConditions::MATCH, 'somevalue', 10);

        expect($this->query->getFilters())
            ->toBeArray()
            ->toHaveKey(FilterVerbs::MIN_SHOULD->value)
            ->and($this->query->getFilters()[FilterVerbs::MIN_SHOULD->value])
            ->toHaveKey('min_count');
    });
});

describe('Filter payload shapes', function () {
    it('builds a match filter with the correct Qdrant shape', function () {
        $transport = Mockery::mock(QdrantTransport::class);
        $transport->shouldReceive('baseUri')->andReturnSelf();
        $points = (new QdrantClient($transport, 'test'))->points();

        $points->must('city', FilterConditions::MATCH, 'London');

        expect($points->getFilters())->toBe([
            'must' => [
                ['key' => 'city', 'match' => ['value' => 'London']],
            ],
        ]);
    });

    it('builds a range filter without wrapping the value under value', function () {
        $transport = Mockery::mock(QdrantTransport::class);
        $transport->shouldReceive('baseUri')->andReturnSelf();
        $points = (new QdrantClient($transport, 'test'))->points();

        $points->must('price', FilterConditions::RANGE, ['gte' => 100, 'lte' => 400]);

        expect($points->getFilters())->toBe([
            'must' => [
                ['key' => 'price', 'range' => ['gte' => 100, 'lte' => 400]],
            ],
        ]);
    });
});

describe('Search request filters', function(){
    it('can send a request with filters', function(){
        $transport = Mockery::mock(QdrantTransport::class);
        $transport->shouldReceive('baseUri')
            ->andReturnSelf();
        $transport->shouldReceive('post')->once()
            ->withAnyArgs()
            ->andReturn($this->validResponse);
        $client = new QdrantClient($transport, "test");

        $search = $client->search()
            ->must('someKey', FilterConditions::MATCH, 'someValue')
            ->vector([123,11,1]);

        $result = $search->get();

        expect($search->getFilters())
            ->toBeArray()
        ->and($search->getSearchPayload())
            ->toBeArray()
            ->toHaveKey('filter')
        ->and($search->getSearchPayload()['filter'])
            ->toBeArray()
            ->toHaveKey(FilterVerbs::MUST->value)
        ->and($search->getFilters()[FilterVerbs::MUST->value])
            ->toHaveCount(1)
        ->and($result)
            ->toBeInstanceOf(PointsCollection::class)
            ->toHaveCount(3);
    });
});
