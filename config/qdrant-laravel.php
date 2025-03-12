<?php
return [
    'default' => env('QDRANT_DEFAULT', 'main'),

    'connections' => [
        'main' => [
            'host' => env('QDRANT_MAIN_HOST', 'http://localhost:6333'),
            'api_key' => env('QDRANT_MAIN_API_KEY', null),
        ],
        'backup' => [
            'host' => env('QDRANT_BACKUP_HOST', 'http://backup-qdrant:6333'),
            'api_key' => env('QDRANT_BACKUP_API_KEY', null),
        ],
    ],

    'default_collection' => env('QDRANT_DEFAULT_COLLECTION', 'default_collection'),
    'default_vector_size' => env('QDRANT_DEFAULT_VECTOR_SIZE', 128),
    'default_distance_metric' => env('QDRANT_DEFAULT_DISTANCE_METRIC', 'cosine'),

    'default_indexes' => [
        'name' => 'keyword',
        'height' => 'float',
        'climate' => 'text',
    ],
];
