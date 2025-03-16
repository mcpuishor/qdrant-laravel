<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum ServerResponseStatus: string
{
    case OK = 'ok';
    case ERROR = 'error';
    case ACKNOWLEDGED = 'acknowledged';
}
