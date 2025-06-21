<?php

use Illuminate\Support\Facades\Artisan;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Enums\FieldType;
use Mcpuishor\QdrantLaravel\Facades\Qdrant;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\Schema\Schema;
use Mcpuishor\QdrantLaravel\Query\Indexes;

beforeEach(function () {
    $this->collectionName = 'test_collection';
    $this->vectorSize = 1536;
    $this->distanceMetric = DistanceMetric::COSINE->value;
    $this->indexes = [
        'field1' => FieldType::KEYWORD->value,
        'field2' => FieldType::INTEGER->value
    ];

    // Create mocks
    $this->qdrantMock = Mockery::mock(QdrantClient::class);
    $this->schemaMock = Mockery::mock(Schema::class);
    $this->clientMock = Mockery::mock(QdrantClient::class);

    // Configure mocks
    $this->qdrantMock->shouldReceive('schema')->andReturn($this->schemaMock);
    $this->qdrantMock->shouldReceive('collection')->with($this->collectionName)->andReturn($this->clientMock);

    // Bind mock to container
    app()->instance('qdrantclient', $this->qdrantMock);

    // Mock the Indexes class
    $this->indexesMock = Mockery::mock(Indexes::class);
    $this->clientMock->shouldReceive('indexes')->andReturn($this->indexesMock);

    // Configure the config values
    config([
        'qdrant-laravel.default_collection' => $this->collectionName,
        'qdrant-laravel.default_vector_size' => $this->vectorSize,
        'qdrant-laravel.default_distance_metric' => $this->distanceMetric,
        'qdrant-laravel.default_indexes' => $this->indexes
    ]);
});

it('can create a collection and add indexes', function () {
    // Mock the schema create method
    $this->schemaMock->shouldReceive('create')
        ->once()
        ->with($this->collectionName, [
            'size' => (int) $this->vectorSize,
            'distance' => $this->distanceMetric
        ])
        ->andReturn(true);

    // Mock the indexes add method for each index
    foreach ($this->indexes as $field => $type) {
        $this->indexesMock->shouldReceive('add')
            ->once()
            ->with($field, Mockery::type(FieldType::class))
            ->andReturn(true);
    }

    // Run the command
    $exitCode = Artisan::call('qdrant:migrate');

    // Assert the command was successful
    expect($exitCode)->toBe(0);
});

it('can rollback a collection and drop indexes', function () {
    // Mock the indexes delete method for each index
    foreach ($this->indexes as $field => $type) {
        $this->indexesMock->shouldReceive('delete')
            ->once()
            ->with($field)
            ->andReturn(true);
    }

    // Mock the schema delete method
    $this->schemaMock->shouldReceive('delete')
        ->once()
        ->with($this->collectionName)
        ->andReturn(true);

    // Run the command with rollback option
    $exitCode = Artisan::call('qdrant:migrate', ['--rollback' => true]);

    // Assert the command was successful
    expect($exitCode)->toBe(0);
});

it('handles invalid distance metric', function () {
    // Run the command with invalid distance metric
    $exitCode = Artisan::call('qdrant:migrate', ['--distance-metric' => 'invalid']);

    // Assert the command failed
    expect($exitCode)->toBe(0);
});

it('handles invalid field type', function () {
    // Mock the schema create method
    $this->schemaMock->shouldReceive('create')
        ->once()
        ->andReturn(true);

    // Configure invalid indexes
    config([
        'qdrant-laravel.default_indexes' => [
            'field1' => 'invalid'
        ]
    ]);

    // Run the command
    $exitCode = Artisan::call('qdrant:migrate');

    // Assert the command was successful (it should continue even with invalid field types)
    expect($exitCode)->toBe(0);
});

it('can use custom options from command line', function () {
    $customCollection = 'custom_collection';
    $customVectorSize = 768;
    $customDistanceMetric = DistanceMetric::DOT->value;
    $customIndexes = json_encode(['custom_field' => FieldType::TEXT->value]);

    // Mock the schema create method with custom options
    $this->schemaMock->shouldReceive('create')
        ->once()
        ->with($customCollection, [
            'size' => (int) $customVectorSize,
            'distance' => $customDistanceMetric
        ])
        ->andReturn(true);

    // Mock the collection method with custom collection
    $this->qdrantMock->shouldReceive('collection')
        ->with($customCollection)
        ->andReturn($this->clientMock);

    // Mock the indexes add method for custom index
    $this->indexesMock->shouldReceive('add')
        ->once()
        ->with('custom_field', Mockery::type(FieldType::class))
        ->andReturn(true);

    // Run the command with custom options
    $exitCode = Artisan::call('qdrant:migrate', [
        '--collection' => $customCollection,
        '--vector-size' => $customVectorSize,
        '--distance-metric' => $customDistanceMetric,
        '--indexes' => $customIndexes
    ]);

    // Assert the command was successful
    expect($exitCode)->toBe(0);
});
