<?php

namespace Mcpuishor\QdrantLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Mcpuishor\QdrantLaravel\QdrantServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            QdrantServiceProvider::class,
        ];
    }
}
