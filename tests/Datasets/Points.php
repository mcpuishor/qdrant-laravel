<?php

use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\PointsCollection;

dataset('points', [
    'data1' => PointsCollection::make([
        new Point(id: 2, vector: [1, 1, 1, 1, 1]),
        new Point(id: 3, vector: [1, 1, 1, 1, 1]),
        new Point(id: 4, vector: [1, 1, 1, 1, 1]),
    ]),
    'data2' => PointsCollection::make([
        new Point(id: 1, vector: [1, 1, 1, 1, 1]),
        new Point(id: 4, vector: [1, 1, 1, 1, 1]),
    ]),
    'data3' => PointsCollection::make([
        new Point(id: 1, vector: [1, 1, 1, 1, 1]),
        new Point(id: 2, vector: [1, 1, 1, 1, 1]),
    ]),
    'data4' => PointsCollection::make([
        new Point(id: 1, vector: [1, 1, 1, 1, 1]),
    ]),
]);
