<?php
return [
    'default' => env('QDRANT_DEFAULT', 'main'),

    'connections' => [
        'main' => [
            'host' => env('QDRANT_HOST', 'http://localhost:6333'),
            'api_key' => env('QDRANT_API_KEY', null),
            'collection' => env('QDRANT_COLLECTION', 'default_collection'),
            'vector_size' => env('QDRANT_VECTOR_SIZE', 128),
        ],
        'backup' => [
            'host' => env('QDRANT_BACKUP_HOST', 'http://backup-qdrant:6333'),
            'api_key' => env('QDRANT_BACKUP_API_KEY', null),
            'collection' => env('QDRANT_BACKUP_COLLECTION', 'default_collection'),
            'vector_size' => env('QDRANT_BACKUP_VECTOR_SIZE', 128),
        ],
    ],

    'default_distance_metric' => env('QDRANT_DEFAULT_DISTANCE_METRIC', 'Cosine'),

    /* allows parameterizaton of integer indexes
     * check the documentation for further details
     * https://qdrant.tech/documentation/concepts/indexing/#parameterized-index
     */

    'index_settings' => [
        'parametrized_integer_index' => [
            'lookup' => true,
            'range' => false
        ],
        'fulltext_index' => [
            'min_token_len' => 2,
            'max_token_len' => 20,
            'lowercase' => true,
        ]
    ],
];
