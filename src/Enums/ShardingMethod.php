<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum ShardingMethod: string
{
    case AUTO = 'auto';
    case CUSTOM = 'custom';
}
