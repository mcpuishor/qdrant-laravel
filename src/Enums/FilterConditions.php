<?php
namespace Mcpuishor\QdrantLaravel\Enums;

enum FilterConditions : string
{
    case MATCH = 'match';
    case RANGE = 'range';
    case GEO_BOUNDING_BOX = 'geo_bounding_box';
    case GEO_POLYGON = 'geo_polygon';
    case GEO_RADIUS = 'geo_radius';
    case VALUES_COUNT = 'values_count';
    case IS_EMPTY = 'is_empty';

}
