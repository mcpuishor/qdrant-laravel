<?php
namespace YourVendor\Qdrant\Commands;

use Illuminate\Console\Command;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Enums\FieldType;
use Mcpuishor\QdrantLaravel\Schema\Schema;

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
        $collectionName = $this->option('collection') ?? config('qdrant.default_collection');
        $vectorSize = $this->option('vector-size') ?? config('qdrant.default_vector_size');
        $distanceMetric = $this->option('distance-metric') ?? config('qdrant.default_distance_metric');
        $indexes = $this->option('indexes') ? json_decode($this->option('indexes'), true) : config('qdrant.default_indexes');

        if (!DistanceMetric::validate($distanceMetric)) {
            $this->error("Invalid distance metric: {$distanceMetric}. Allowed: " . implode(', ', DistanceMetric::values()));
            return;
        }

        if ($this->option('rollback')) {
            $this->rollback($collectionName, $indexes);
            return;
        }

        $this->info("Creating collection: {$collectionName}");

        Schema::create($collectionName, [
            'vector' => [
                'size' => (int) $vectorSize,
                'distance' => $distanceMetric
            ]
        ]);

        foreach ($indexes as $field => $type) {
            if (!FieldType::validate($type)) {
                $this->error("Invalid field type for {$field}: {$type}. Allowed: " . implode(', ', FieldType::values()));
                continue;
            }
            Schema::addIndex($collectionName, $field, FieldType::from($type));
            $this->info("Index created for field: {$field} ({$type}).");
        }

        $this->info("Qdrant migration completed for collection: {$collectionName}");
    }

    protected function rollback(string $collectionName, array $indexes)
    {
        $this->info("Rolling back migration for collection: {$collectionName}");

        foreach ($indexes as $field => $type) {
            Schema::dropIndex($collectionName, $field);
            $this->info("Index dropped for field: {$field}.");
        }

        Schema::drop($collectionName);
        $this->info("Collection {$collectionName} deleted.");
    }
}
