<?php
namespace Mcpuishor\QdrantLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Client extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qdrantclient';
    }
}
