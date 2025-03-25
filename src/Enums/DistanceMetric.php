<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum DistanceMetric: string
{
    case COSINE = 'Cosine';
    case EUCLIDEAN = 'Euclid';
    case DOT = 'Dot';
    case MANHATTAN = 'Manhattan';

    public static function values(): array
    {
        return array_column(DistanceMetric::cases(), 'value');
    }

    public static function validate(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
