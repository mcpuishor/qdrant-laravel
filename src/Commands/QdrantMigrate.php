<?php
namespace Mcpuishor\QdrantLaravel\Commands;

use Illuminate\Console\Command;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Enums\FieldType;
use Mcpuishor\QdrantLaravel\Facades\Qdrant;

class QdrantMigrate extends Command
{
    protected $signature = 'qdrant:migrate
                            {--collection=}
                            {--vector-size=}
                            {--distance-metric=}
                            {--indexes=}
                            {--rollback}';

    protected $description = 'Run Qdrant schema migrations, including index management.';

    public function handle()
    {
        $collectionName = $this->option('collection') ?? config('qdrant-laravel.default_collection');
        $vectorSize = $this->option('vector-size') ?? config('qdrant-laravel.default_vector_size');
        $distanceMetric = $this->option('distance-metric') ?? config('qdrant-laravel.default_distance_metric');
        $indexes = $this->option('indexes') ? json_decode($this->option('indexes'), true) : config('qdrant-laravel.default_indexes');

        if (!DistanceMetric::validate($distanceMetric)) {
            $this->error("Invalid distance metric: {$distanceMetric}. Allowed: " . implode(', ', DistanceMetric::values()));
            return;
        }

        if ($this->option('rollback')) {
            $this->rollback($collectionName, $indexes);
            return;
        }

        $this->info("Creating collection: {$collectionName}");

        Qdrant::schema()->create($collectionName, [
                'size' => (int) $vectorSize,
                'distance' => $distanceMetric
            ]);

        foreach ($indexes as $field => $type) {
            if (!FieldType::validate($type)) {
                $this->error("Invalid field type for {$field}: {$type}. Allowed: " . implode(', ', FieldType::values()));
                continue;
            }
            try {
                Qdrant::collection($collectionName)->indexes()->add($field, FieldType::from($type));
                $this->info("Index created for field: {$field} ({$type}).");
            } catch (\ValueError $e) {
                $this->error("Failed to create index for field {$field}: {$e->getMessage()}");
                continue;
            }
        }

        $this->info("Qdrant migration completed for collection: {$collectionName}");
    }

    protected function rollback(string $collectionName, array $indexes)
    {
        $this->info("Rolling back migration for collection: {$collectionName}");

        foreach ($indexes as $field => $type) {
            Qdrant::collection($collectionName)->indexes()->delete($field);
            $this->info("Index dropped for field: {$field}.");
        }

        Qdrant::schema()->delete($collectionName);
        $this->info("Collection {$collectionName} deleted.");
    }
}
