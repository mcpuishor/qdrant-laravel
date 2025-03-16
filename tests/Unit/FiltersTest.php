<?php
describe('Filter building', function(){
    beforeEach(function(){
        $this->builder = Qdrant::collection('test');
    });

    it('can add a must filter with values', function () {
        $this->builder->filter()
            ->must('someKey', FilterConditions::MATCH, 'someValue')
            ->must('otherKey', FilterConditions::MATCH, 'someValue');

        expect($this->builder->getFilters())
            ->toBeArray()
            ->and($this->builder->getFilters()[FilterVerbs::MUST->value])
            ->toHaveCount(2);
    });

    it('can add a must filter via a callable', function(){
        $this->builder->filter()
            ->must(fn($q) => $q->must('someNestedKey', FilterConditions::MATCH, 'someNestedValue'));

        expect($this->builder->getFilters())
            ->toBeArray()
            ->toHaveKey( FilterVerbs::MUST->value )
            ->and($this->builder->getFilters()[ FilterVerbs::MUST->value ])
            ->toHaveCount(1);
    });

    it('can add a should filter with values', function () {
        $this->builder->filter()
            ->should('someKey', FilterConditions::MATCH, 'someValue')
            ->should('someNew', FilterConditions::MATCH, 'someValue');

        expect($this->builder->getFilters())
            ->toBeArray()
            ->toHaveKey(FilterVerbs::SHOULD->value)
            ->and($this->builder->getFilters()[FilterVerbs::SHOULD->value])
            ->toHaveCount(2);
    });

    it('can add a must not filter with values', function () {
        $this->builder->filter()
            ->mustNot('someKey', FilterConditions::MATCH, 'someValue')
            ->mustNot('someNew', FilterConditions::MATCH, 'someValue');

        expect($this->builder->getFilters())
            ->toBeArray()
            ->toHaveKey(FilterVerbs::MUST_NOT->value)
            ->and($this->builder->getFilters()[FilterVerbs::MUST_NOT->value])
            ->toHaveCount(2);
    });

    it('can add a min should filter', function(){
        $this->builder->filter()
            ->minShould('someKey', FilterConditions::MATCH, 'somevalue', 1)
            ->minShould('someotherkey', FilterConditions::MATCH, 'somevalue', 10);

        expect($this->builder->getFilters())
            ->toBeArray()
            ->toHaveKey(FilterVerbs::MIN_SHOULD->value)
            ->and($this->builder->getFilters()[FilterVerbs::MIN_SHOULD->value])
            ->toHaveKey('min_count');
    });
})->skip();
