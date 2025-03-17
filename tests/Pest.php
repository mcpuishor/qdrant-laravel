<?php

use Orchestra\Testbench\TestCase;

uses(TestCase::class)->in("Feature", "Unit");

function getPackageProviders($app)
{
    return [
        Mcpuishor\QdrantLaravel\QdrantServiceProvider::class,
    ];
}

beforeEach(function () {

    config()->set('qdrant-laravel.index_settings.fulltext_index', [
        'min_token_len' => 2,
        'max_token_len' => 20,
        'lowercase' => true,
    ]);

    config()->set('qdrant-laravel.index_settings.parametrized_integer_index', [
        'lookup' => true,
        'range' => false
    ]);

});
