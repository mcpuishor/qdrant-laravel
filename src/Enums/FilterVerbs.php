<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum FilterVerbs : string
{
    case MUST = 'must';
    case MUST_NOT = 'must_not';
    case SHOULD = 'should';
    case MIN_SHOULD = 'min_should';
}
