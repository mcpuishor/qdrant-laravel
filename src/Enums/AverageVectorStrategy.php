<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum AverageVectorStrategy: string
{
    case AVERAGEVECTOR = 'average_vector';
    case BESTSCORE = 'best_score';


    static function default(): self
    {
        return self::AVERAGEVECTOR;
    }
}
