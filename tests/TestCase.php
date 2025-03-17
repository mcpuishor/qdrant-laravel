<?php

namespace Mcpuishor\QdrantLaravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Mcpuishor\QdrantLaravel\QdrantServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            QdrantServiceProvider::class,
        ];
    }
}
