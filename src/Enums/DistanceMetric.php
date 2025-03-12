<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum DistanceMetric: string
{
    case COSINE = 'cosine';
    case EUCLIDEAN = 'euclidean';
    case DOT = 'dot';

    public static function values(): array
    {
        return array_column(DistanceMetric::cases(), 'value');
    }

    public static function validate(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
