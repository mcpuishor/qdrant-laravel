<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\ServiceProvider;

class QdrantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(QdrantClient::class, function ($app) {
            return new QdrantClient(config('qdrant-laravel.default'));
        });

        $this->app->singleton('qdrant', function ($app) {
            return new QdrantClient(config('qdrant-laravel.default'));
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
