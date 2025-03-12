<?php
namespace Mcpuishor\QdrantLaravel;

use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use Mcpuishor\QdrantLaravel\Enums\FieldType;
use InvalidArgumentException;
use Illuminate\Support\Traits\Macroable;

class QdrantSchema
{
    use Macroable;

    protected QdrantClient $client;

    public function __construct(QdrantClient $client)
    {
        $this->client = $client;
    }

    public function create(string $name, array $vectors, array $options = [])
    {
        foreach ($vectors as $vectorName => &$vectorConfig) {
            if (isset($vectorConfig['distance']) && !DistanceMetric::validate($vectorConfig['distance'])) {
                throw new InvalidArgumentException("Invalid distance metric: {$vectorConfig['distance']}. Allowed: " . implode(', ', DistanceMetric::values()));
            }
        }

        return $this->client->request('PUT', "/collections/{$name}", ['json' => $vectors + $options]);
    }

    public function drop(string $name)
    {
        return $this->client->request('DELETE', "/collections/{$name}");
    }

    public function addIndex(string $collection, string $field, FieldType $type)
    {
        return $this->client->request('POST', "/collections/{$collection}/index", [
            'json' => [
                'field_name' => $field,
                'field_type' => $type->value,
            ]
        ]);
    }

    public function dropIndex(string $collection, string $field)
    {
        return $this->client->request('DELETE', "/collections/{$collection}/index/{$field}");
    }
}
