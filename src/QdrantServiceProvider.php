<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Http\Client\Factory as Client;
use Illuminate\Support\ServiceProvider;
use Mcpuishor\QdrantLaravel\Schema\Schema;

class QdrantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(QdrantTransport::class, function ($app) {
            return new QdrantTransport(
                connection: config('qdrant-laravel.default'),
            );
        });

        $this->app->singleton('qdrantclient', function ($app) {
            return new QdrantClient(
                app()->make(QdrantTransport::class)
            );
        });

        $this->app->bind('qdrantschema', function ($app) {
            return new Schema(
                app()->make(QdrantTransport::class)
            );
        });

        $this->mergeConfigFrom(__DIR__.'/../config/qdrant-laravel.php', 'qdrant-laravel');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/qdrant-laravel.php' => config_path('qdrant-laravel.php'),
        ], 'qdrant-laravel-config');
    }
}
