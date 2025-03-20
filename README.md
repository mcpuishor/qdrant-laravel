# Qdrant for Laravel
WARNING! This package is under heavy development and it should not be used just yet. APIs and functionality changes may break applications. 

## Introduction
This package provides an elegant, fluent interface for interacting with the [Qdrant Vector Database](https://qdrant.tech/) in Laravel.
This initial version is handling only single vector collections. 

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
Modify your `.env` file with your Qdrant host details:
```ini
QDRANT_MAIN_HOST=http://localhost:6333
QDRANT_MAIN_API_KEY=
QDRANT_DEFAULT_COLLECTION=default_collection
QDRANT_DEFAULT_VECTOR_SIZE=128
QDRANT_DEFAULT_DISTANCE_METRIC=cosine
```

## Configuration
The `config/qdrant-laravel.php` file allows multiple connections:
```php
return [
    'default' => env('QDRANT_DEFAULT', 'main'),
    'connections' => [
        'main' => [
            'host' => env('QDRANT_MAIN_HOST', 'http://localhost:6333'),
            'api_key' => env('QDRANT_MAIN_API_KEY', null),
        ],
    ],
    'default_collection' => 'default_collection',
    'default_vector_size' => 128,
    'default_distance_metric' => 'cosine',
    'default_indexes' => [
        'name' => 'keyword',
        'height' => 'float',
    ],
];
```

## Schema Management (Migrations)

### Creating a new collection using the default connection

```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;
use \Mcpuishor\QdrantLaravel\Enums\DistanceMetric;

$collection = Schema::create(
                   name: "new_collection",
                   vector: [
                        'size' => 128,
                        'distance' => DistanceMetric::COSINE
                   ]
                );
```
### Creating a new collection on a different connection 
When the server connection is different from teh default one, the 
connection must be specified when creating the collection. The connection 
must be defined in the ``config\qdrant-laravel.php`` file.

```php
use \Mcpuishor\QdrantLaravel\Schema;
use \Mcpuishor\QdrantLaravel\QdrantTransport;
use \Mcpuishor\QdrantLaravel\Enums\DistanceMetric;

$collection = Schema::make( new \Mcpuishor\QdrantLaravel\QdrantTransport('backup') )
                ->create(
                   name: "new_collection",
                   vector: [
                        'size' => 128,
                        'distance' => DistanceMetric::COSINE
                   ]
                );
```

## Updating a collection

## Deleting a collection

## Indexing a collection
Indexes in a Qdrant vector collection are created on the payload for each vector.
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



## Artisan commands
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

