<?php

use Mcpuishor\QdrantLaravel\Enums\FieldType;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;
use Mcpuishor\QdrantLaravel\DTOs\Response;

beforeEach(function () {
    $this->testCollectionName = 'test';
    $this->fieldName = 'field';
    $this->transport = Mockery::mock(QdrantTransport::class);
    $this->query = new QdrantClient($this->transport, $this->testCollectionName);
});

it('can create a payload index', function(){
    $this->transport->shouldReceive('request')
        ->withArgs([
            'PUT',
            "/collections/{$this->testCollectionName}/index",
            [
                "json" => [
                    'field_name' => $this->fieldName,
                    'field_schema' => [
                        'type' => FieldType::TEXT->value,
                        'on_disk' => false,
                    ],
                ]
            ]
        ])
        ->andReturn(new Response([
            'time' => 1,
            'status' => 'ok',
            'result' => [
                'status' => 'acknowledged',
                'operation_id' => 1,
            ],
        ]));

    $result = $this->query->indexes()->add($this->fieldName, FieldType::TEXT);

    expect($result)->toBeTrue();
});

it('can delete a payload index', function(){
    $this->transport->shouldReceive('request')
        ->withArgs([
            'DELETE',
            "/collections/{$this->testCollectionName}/index/{$this->fieldName}"
        ])
        ->andReturn(new Response([
            'time' => 1,
            'status' => 'ok',
            'result' => [
                'status' => 'acknowledged',
                'operation_id' => 1,
            ],
        ]));

    $result = $this->query->indexes()->delete($this->fieldName);

    expect($result)->toBetrue();
});

it('can create a payload index on disk', function(){
    $this->transport->shouldReceive('request')
        ->withArgs([
            'PUT',
            "/collections/{$this->testCollectionName}/index",
            [
                "json" => [
                    'field_name' => $this->fieldName,
                    'field_schema' => [
                        'type' => FieldType::TEXT->value,
                        'on_disk' => true,
                    ],
                ]
            ]
        ])
        ->andReturn(new Response([
            'time' => 1,
            'status' => 'ok',
            'result' => [
                'status' => 'acknowledged',
                'operation_id' => 1,
            ],
        ]));

    $result = $this->query->indexes()->onDisk()->add($this->fieldName, FieldType::TEXT);

    expect($result)->toBeTrue();
});

it('can create a full-text index', function(){
    $this->transport->shouldReceive('request')
        ->withArgs([
            'PUT',
            "/collections/{$this->testCollectionName}/index",
            [
                "json" => [
                    'field_name' => $this->fieldName,
                    'field_schema' => [
                        'type' => FieldType::TEXT->value,
                        'on_disk' => true,
                    ],
                ]
            ]
        ])
        ->andReturn(new Response([
            'time' => 1,
            'status' => 'ok',
            'result' => [
                'status' => 'acknowledged',
                'operation_id' => 1,
            ],
        ]));

    $result = $this->query->indexes()->fulltext($this->fieldName);

    expect($result)->toBeTrue();
});
