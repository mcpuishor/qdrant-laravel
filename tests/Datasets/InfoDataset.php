<?php

dataset('collectionInfo', [
    'green_status' => [
        ['result' => [
            'status' => 'green',
            'optimizer_status' => 'ok',
            'indexed_vectors_count' => 1000,
            'points_count' => 1200,
            'segments_count' => 5,
            'config' => [
                'params' => [
                    'vectors' => [
                        'dense' => [
                            'size' => 768,
                            'distance' => 'Cosine',
                        ],
                    ],
                    'shard_number' => 1,
                    'replication_factor' => 1,
                    'write_consistency_factor' => 1,
                    'on_disk_payload' => false,
                ],
                'hnsw_config' => [
                    'm' => 16,
                    'ef_construct' => 100,
                    'full_scan_threshold' => 10000,
                    'max_indexing_threads' => 0,
                    'on_disk' => false,
                ],
                'optimizers_config' => [
                    'deleted_threshold' => 0.2,
                    'vacuum_min_vector_number' => 1000,
                    'default_segment_number' => 2,
                    'max_segment_size' => null,
                    'memmap_threshold' => null,
                    'indexing_threshold' => 20000,
                    'max_optimization_threads' => null,
                ],
                'wal_config' => [
                    'wal_capacity_mb' => 32,
                    'wal_segments_ahead' => 0,
                ],
                'quantization_config' => null,
                'strict_mode_config' => [
                    'enabled' => false,
                ],
            ],
            'payload_schema' => [
                'id' => ['data_type' => 'keyword'],
                'metadata' => ['data_type' => 'json'],
            ]], ],
    ],
    'yellow_status' => [
        ['result' => [
            'status' => 'yellow',
            'optimizer_status' => 'optimizing',
            'indexed_vectors_count' => 800,
            'points_count' => 1000,
            'segments_count' => 4,
            'config' => [
                'params' => [
                    'vectors' => [
                        'dense' => [
                            'size' => 384,
                            'distance' => 'Dot',
                        ],
                    ],
                    'shard_number' => 2,
                    'replication_factor' => 2,
                    'write_consistency_factor' => 1,
                    'on_disk_payload' => true,
                ],
                'hnsw_config' => [
                    'm' => 16,
                    'ef_construct' => 100,
                    'full_scan_threshold' => 10000,
                    'max_indexing_threads' => 4,
                    'on_disk' => true,
                ],
                'optimizers_config' => [
                    'deleted_threshold' => 0.1,
                    'vacuum_min_vector_number' => 500,
                    'default_segment_number' => 3,
                    'max_segment_size' => 10000,
                    'memmap_threshold' => 10000,
                    'indexing_threshold' => 10000,
                    'max_optimization_threads' => 2,
                ],
                'wal_config' => [
                    'wal_capacity_mb' => 64,
                    'wal_segments_ahead' => 2,
                ],
                'quantization_config' => null,
                'strict_mode_config' => [
                    'enabled' => true,
                ],
            ],
            'payload_schema' => [
                'title' => ['data_type' => 'keyword'],
                'content' => ['data_type' => 'text'],
            ]]],
    ],
    'grey_status' => [['result' => [
        'status' => 'grey',
        'optimizer_status' => 'pending',
        'indexed_vectors_count' => 0,
        'points_count' => 0,
        'segments_count' => 0,
        'config' => [
            'params' => [
                'vectors' => [
                    'embedding' => [
                        'size' => 1536,
                        'distance' => 'Euclidean',
                    ],
                ],
                'shard_number' => 1,
                'replication_factor' => 1,
                'write_consistency_factor' => 1,
                'on_disk_payload' => false,
            ],
            'hnsw_config' => [
                'm' => 16,
                'ef_construct' => 100,
                'full_scan_threshold' => 10000,
                'max_indexing_threads' => 0,
                'on_disk' => false,
            ],
            'optimizers_config' => [
                'deleted_threshold' => 0.2,
                'vacuum_min_vector_number' => 1000,
                'default_segment_number' => 2,
                'max_segment_size' => null,
                'memmap_threshold' => null,
                'indexing_threshold' => 20000,
                'max_optimization_threads' => null,
            ],
            'wal_config' => [
                'wal_capacity_mb' => 32,
                'wal_segments_ahead' => 0,
            ],
            'quantization_config' => null,
            'strict_mode_config' => [
                'enabled' => false,
            ],
        ],
        'payload_schema' => [],
    ], ],
    ],
    'red_status' => [['result' => [
        'status' => 'red',
        'optimizer_status' => 'error',
        'indexed_vectors_count' => 500,
        'points_count' => 500,
        'segments_count' => 2,
        'config' => [
            'params' => [
                'vectors' => [
                    'dense' => [
                        'size' => 768,
                        'distance' => 'Cosine',
                    ],
                ],
                'shard_number' => 1,
                'replication_factor' => 1,
                'write_consistency_factor' => 1,
                'on_disk_payload' => false,
            ],
            'hnsw_config' => [
                'm' => 16,
                'ef_construct' => 100,
                'full_scan_threshold' => 10000,
                'max_indexing_threads' => 0,
                'on_disk' => false,
            ],
            'optimizers_config' => [
                'deleted_threshold' => 0.2,
                'vacuum_min_vector_number' => 1000,
                'default_segment_number' => 2,
                'max_segment_size' => null,
                'memmap_threshold' => null,
                'indexing_threshold' => 20000,
                'max_optimization_threads' => null,
            ],
            'wal_config' => [
                'wal_capacity_mb' => 32,
                'wal_segments_ahead' => 0,
            ],
            'quantization_config' => null,
            'strict_mode_config' => [
                'enabled' => false,
            ],
        ],
        'payload_schema' => [
            'id' => ['data_type' => 'keyword'],
            'metadata' => ['data_type' => 'json'],
            'embedding' => ['data_type' => 'float[]'],
        ]], ],
    ],
]);

dataset('status_data', [
    'green' => ['status' => 'green', 'optimizer_status' => 'ok'],
    'yellow' => ['status' => 'yellow', 'optimizer_status' => 'optimizing'],
    'grey' => ['status' => 'grey', 'optimizer_status' => 'pending'],
    'red' => ['status' => 'red', 'optimizer_status' => 'error'],
]);

dataset('vector_config', [
    'single_vector' => [
        'vectors' => [
            'dense' => [
                'size' => 768,
                'distance' => 'Cosine',
            ],
        ],
    ],
    'multi_vector' => [
        'vectors' => [
            'text' => [
                'size' => 384,
                'distance' => 'Dot',
            ],
            'image' => [
                'size' => 512,
                'distance' => 'Cosine',
            ],
        ],
    ],
    'large_vector' => [
        'vectors' => [
            'embedding' => [
                'size' => 1536,
                'distance' => 'Euclidean',
            ],
        ],
    ],
]);

dataset('payload_schemas', [
    'standard_schema' => [
        'id' => ['data_type' => 'keyword'],
        'metadata' => ['data_type' => 'json'],
    ],
    'text_schema' => [
        'title' => ['data_type' => 'keyword'],
        'content' => ['data_type' => 'text'],
    ],
    'vector_schema' => [
        'id' => ['data_type' => 'keyword'],
        'metadata' => ['data_type' => 'json'],
        'embedding' => ['data_type' => 'float[]'],
    ],
    'empty_schema' => [],
]);
