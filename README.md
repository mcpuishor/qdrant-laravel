# Qdrant for Laravel

## Introduction
This package provides an elegant, fluent interface for interacting with the [Qdrant Vector Database](https://qdrant.tech/) in Laravel. Qdrant is a vector similarity search engine that makes it easy to store and search for embeddings, making it ideal for AI-powered applications.

Key features:
- Simple collection management
- Fluent search API with filtering and grouping
- Efficient point operations (insert, upsert, delete)
- Laravel Facade support
- Convenient payload handling

## Installation

### 1. Install via Composer

```sh
composer require mcpuishor/qdrant-laravel
```

### 2. Publish the Configuration File
```sh
php artisan vendor:publish --tag=qdrant-laravel-config
```
This will create a `config/qdrant-laravel.php` file where you can set your Qdrant connections and defaults.

### 3. Set Up Your `.env` File
Update your `.env` file with your Qdrant host details:
```env
QDRANT_DEFAULT=main
QDRANT_HOST=http://localhost:6333
QDRANT_COLLECTION=collection_name
QDRANT_VECTOR_SIZE=1536
QDRANT_DEFAULT_DISTANCE_METRIC=Cosine
```

## Configuration
The `config/qdrant-laravel.php` file allows multiple connections:
```php
return [
    'default' => env('QDRANT_DEFAULT', 'main'),
    
    'connections' => [
        'main' => [
            'host' => env('QDRANT_HOST', 'http://localhost:6333'),
            'api_key' => env('QDRANT_API_KEY', null),
            'collection' => env('QDRANT_COLLECTION', 'default_collection'),
            'vector_size' => env('QDRANT_VECTOR_SIZE', 128),
        ],
    ],
    
    'default_distance_metric' => env('QDRANT_DEFAULT_DISTANCE_METRIC', 'Cosine'),
];
```

## Schema Management (Migrations)

### Creating a new collection

A collection must contain at least one vector. An optional parameter `options` can contain additional
parameters described as an associative array. See the [Qdrant documentation](https://api.qdrant.tech/api-reference/collections/create-collection) for details. The options can be specified using arrays
or DataObjects defined in the package.

The response is a boolean value, unless an exception is thrown.

```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;
use \Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use \Mcpuishor\QdrantLaravel\DTOs\Vector;

$vector = Vector::fromArray([
            'size' => 128,
            'distance' => DistanceMetric::COSINE
       ]);

$response = Schema::create(
                   name: "new_collection",
                   vector: $vector,
                   options: []
                );

if ($response) {
    echo "Schema created successfully";
}
```
### Creating a new collection on a different connection 
You can switch the connection at runtime. The connection must be defined in the 
`config\qdrant-laravel.php` file.

```php
use \Mcpuishor\QdrantLaravel\Schema;
use \Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use \Mcpuishor\QdrantLaravel\DTOs\Vector;

$vector = Vector::fromArray([
            'size' => 128,
            'distance' => DistanceMetric::COSINE
       ]);

$response = Schema::connection('backup')
                ->create(
                   name: "new_collection",
                   vector: $vector,
                );

if ($response) {
    echo "Schema created successfully";
}

```

### Creating a collection with multiple vectors
A collection can contain multiple vectors per point. They need to be passed on to the `Schema::create` 
as an array containing the definitions of each vector. The vectors can have different definitions. The 
optional parameters can be specified using Data Objects defined in the package.

```php 
use \Mcpuishor\QdrantLaravel\Schema;
use \Mcpuishor\QdrantLaravel\QdrantTransport;
use \Mcpuishor\QdrantLaravel\Enums\DistanceMetric;
use \Mcpuishor\QdrantLaravel\DTOs\Vector;
use \Mcpuishor\QdrantLaravel\DTOs\HnswConfig;

$vector1 = Vector::fromArray([
            'size' => 128,
            'distance' => DistanceMetric::COSINE
            //optional parameters
            'on_disk' => true,
            ]);

$vector2 = Vector::fromArray([
            'size' => 1024,
            'distance' => DistanceMetric::COSINE,
            //optional parameters
            'hsnw_config' => Hnswconfig::fromArray([
                    'm' => 10,
                    'ef_construct' => 4,
                    'on_disk' => true,
                ]),
            ]);

$response = Schema::create(
               name: "new_collection",
               vector: array($vector1, $vector2),
            );

if ($response) {
    echo "Schema created successfully";
}

```

## Deleting a collection
To delete a collection, you can call the `delete` method on the `Schema` facade.
It returns a `Mcpuishor\QdrantLaravel\DTOs\Response` object.

```php
    use \Mcpuishor\QdrantLaravel\Facades\Schema;
    
    $result = Schema::delete('collection_name');
    
    if ($result) {
        echo "Collection has been successfully deleted.";
    }
```

## Collection existence 
To check if the collection defined in the config on the current connection exists: 

```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;
  
    if ( Schema::exists() ) {
        echo "Collection exists.";
    }
```

At the same time, you can check the existence of a different collection on the same connection: 

```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;
  
    if ( Schema::exists( 'another_collection' ) ) {
        echo "Collection 'another_collection' exists.";
    }
```


## Updating a collection
Updating parameters on an existing collection can be done in a similar fashion to creating one. The parameters updated 
can be specified using arrays or Data Objects defined in the package. 

If the collection has a single unnamed vector, use an empty string as a key for the vector options that must be updated.

```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;
use \Mcpuishor\QdrantLaravel\DTOs\Vector;
use \Mcpuishor\QdrantLaravel\DTOs\HnswConfig;

Schema::update(
    vectors: [
        "" => [
            Vector::fromArray([
                'on_disk' => true,
                'hnsw_config'=> HnswConfig::fromArray([
                    'm' => 32,
                ])
            ])
        ]
    ],
    options: [
        'hnsw_config'=> HnswConfig::fromArray([
                        'm' => 32,
                    ])
    ]
);
```

Updating a different collection than the default one defined in the current connection, you must specify the collection name as a parameter.

```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;

Schema::update(
    collection: 'collection_name',
    vectors: [...],
    options: [...]
);
```

## Indexing a collection
Indexes in a Qdrant collection are created on the payload for each point.
For more details see the [Qdrant documentation](https://qdrant.tech/documentation/concepts/indexing/). 

### Creating an index
To create a payload index over a field:
```php
use \Mcpuishor\QdrantLaravel\Facades\Client as Qdrant;
use \Mcpuishor\QdrantLaravel\Enums\FieldType;

$result = Qdrant::indexes()->add('field_name', FieldType::KEYWORD);
```
It returns ``true`` if the operation was successful, or ``false`` otherwise. 

You can use dot notation to create indexes over nested fields.

By default, indexes are stored in memory. If you have large indexes, and they
need to be stored on the disk, you can use the ``->onDisk()`` method before 
creating the index. Choose carefully when to store an index on the disk, 
as this will introduce some latency in your future queries.

### Parameterized integer indexes
Qdrant v1.8.0 has introduced a parameterized variant of the integer index. 
To turn the parameterized index on you can call the ``->parameterized()`` 
method before creating an ``integer`` index. This setting is used only for ``integer`` fields
in the payload. 

Values of the ``lookup`` and ``range`` can be toggled in the ``config\qdrant-laravel.php`` file.
For more information on parameterized integer indexes and how they affect performance
check the [Qdrant documentation](https://qdrant.tech/documentation/concepts/indexing/#parameterized-index)

```php
    $result = Qdrant::indexes()->parameterized()->add('field_name', FieldType::INTEGER);
```
It returns ``true`` if the operation was successful, or ``false`` otherwise.

### Full-text indexes
Qdrant supports full-text search for string payload. Full-text index allows you to filter points by 
the presence of a word or a phrase in the payload field.

````php
    use \Mcpuishor\QdrantLaravel\Enums\TokenizerType;
    use \Mcpuishor\QdrantLaravel\Facades\Client as Qdrant;
    
    $result = Qdrant::indexes()->fulltext('text_field_name', TokenizerType::WORD);
````
It returns ``true`` if the operation was successful, or ``false`` otherwise.

### Deleting an index
````php
    use \Mcpuishor\QdrantLaravel\Facades\Client as Qdrant;
    
    $result = Qdrant::indexes()->delete('payload_field');
````
It returns ``true`` if the operation was successful, or ``false`` otherwise.

## Searching
The package provides a fluent interface for searching vectors in your Qdrant collection.

### Basic Vector Search
To perform a simple search with a vector:
### Creating a Collection with indexes
```sh
php artisan qdrant:migrate --collection=plants --vector-size=256 --distance-metric=euclidean --indexes='{"species":"text","age":"integer"}'
```

### Rolling Back a Migration (Dropping Collection & Indexes)
```sh
php artisan qdrant:migrate --rollback --collection=plants
```

## Extending with Macros
The query builder and client are **Macroable**, allowing custom methods:

```php
use Mcpuishor\QdrantLaravel\QdrantClient;

QdrantClient::macro('byClimate', function ($climate) {
    return $this->where('climate', '=', $climate);
});

$results = Qdrant::collection('plants')->byClimate('tropical')->get();
```

## Conclusion
This package simplifies working with Qdrant in Laravel, making it easy to integrate **vector search** and **AI-powered applications**. Contributions are welcome!

---
### **License**
This package is open-source and available under the [MIT License](LICENSE).

