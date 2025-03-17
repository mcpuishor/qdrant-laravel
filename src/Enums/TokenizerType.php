<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum TokenizerType: string
{
    case WORD = 'word';
    case WHITESPACE = 'whitespace';
    case PREFIX = 'prefix';
    case MULTILINGUAL = 'multilingual';
}
