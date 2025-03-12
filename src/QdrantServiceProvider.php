<?php
namespace Mcpuishor\QdrantLaravel;

use Illuminate\Support\ServiceProvider;

class QdrantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(QdrantClient::class, function ($app) {
            return new QdrantClient(config('qdrant'));
        });

        $this->mergeConfigFrom(__DIR__.'/../config/qdrant.php', 'qdrant');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/qdrant.php' => config_path('qdrant.php'),
        ], 'config');
    }
}
