# Laravel Qdrant Package

## Introduction
This package provides an elegant, fluent interface for interacting with the [Qdrant Vector Database](https://qdrant.tech/) in Laravel. It supports:

- **Dynamic collections and vector sizes**
- **Vector similarity search**
- **Filtering, sorting, and pagination**
- **Index creation and management**
- **Macroable Query Builder**
- **Migrations for Qdrant collections**

## Installation
### 1. Install via Composer
```sh
composer require mcpuishor/qdrant-laravel
```

### 2. Publish the Configuration File
```sh
php artisan vendor:publish --tag=qdrant-laravel-config
```
This will create a `config/qdrant.php` file where you can set your Qdrant connections and defaults.

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
The `config/qdrant.php` file allows multiple connections:
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

## Usage

### 1. Performing a Basic Query
```php
use Qdrant;

$results = Qdrant::collection('plants')
    ->where('type', '=', 'bamboo')
    ->orderBy('size', 'desc')
    ->limit(5)
    ->get();
```

### 2. Vector Similarity Search
```php
$results = Qdrant::collection('plants')
    ->searchVector([0.1, 0.2, 0.3, 0.4])
    ->limit(10)
    ->get();
```

### 3. Counting Matching Records
```php
$count = Qdrant::collection('plants')
    ->where('climate', '=', 'tropical')
    ->count();
```

### 4. Deleting Records
```php
$deleted = Qdrant::collection('plants')
    ->where('climate', '=', 'tropical')
    ->delete();
```

## Schema Management (Migrations)

### Creating a Collection with Indexes
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
use Mcpuishor\QdrantLaravel\QdrantQueryBuilder;

QdrantQueryBuilder::macro('byClimate', function ($climate) {
    return $this->where('climate', '=', $climate);
});

$results = Qdrant::collection('plants')->byClimate('tropical')->get();
```

## Conclusion
This package simplifies working with Qdrant in Laravel, making it easy to integrate **vector search** and **AI-powered applications**. Contributions are welcome!

---
### **License**
This package is open-source and available under the [MIT License](LICENSE).

