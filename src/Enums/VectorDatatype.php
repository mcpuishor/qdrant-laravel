<?php

namespace Mcpuishor\QdrantLaravel\Enums;

enum VectorDatatype: string
{
    case FLOAT32 = 'float32';
    case FLOAT16 = 'float16';
    case UINT8 = 'uint8';

    public function bytes(): int
    {
        return match($this) {
            self::FLOAT32 => 4,
            self::FLOAT16 => 2,
            self::UINT8 => 1,
        };
    }

    public function description(): string
    {
        return match($this) {
            self::FLOAT32 => 'Single-precision floating point numbers, 4 bytes',
            self::FLOAT16 => 'Half-precision floating point numbers, 2 bytes',
            self::UINT8 => 'Unsigned 8-bit integers, 1 byte. Expected range [0, 255]',
        };
    }
}
