<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum FieldType: string
{
    case TEXT = 'text';
    case KEYWORD = 'keyword';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case GEO = 'geo';
    case DATETIME = 'datetime';
    case UUID = 'uuid';

    public static function values(): array
    {
        return array_column(FieldType::cases(), 'value');
    }

    public static function validate(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
