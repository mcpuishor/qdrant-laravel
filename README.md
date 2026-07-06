# Qdrant for Laravel

## Introduction
This package provides an elegant, fluent interface for interacting with the [Qdrant Vector Database](https://qdrant.tech/) in Laravel. Qdrant is a vector similarity search engine that makes it easy to store and search for embeddings, making it ideal for AI-powered applications.

Key features:
- Simple collection management
- Fluent search API with filtering and grouping
- Efficient point operations (insert, upsert, delete)
- Vector operations (update, delete)
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

Updating the collection defined in the `config\qdrant-laravel.php`:
```php
use \Mcpuishor\QdrantLaravel\Facades\Schema;
use \Mcpuishor\QdrantLaravel\DTOs\HnswConfig;
use \Mcpuishor\QdrantLaravel\DTOs\Collection\Params;

Schema::update(
    vectors: [

    ], 
    options: [
       'hnsw_config' => HnswConfig::fromArray([
                'm' => 100,
                'ef_construct' => 5,
            ]),
       'params' => Params::fromArray([
                'replication_factor' => 4,
                'on_disk_payload' => true,
            ]),
    ]
);
```


## Indexing a collection
Indexes in a Qdrant vector collection are created on the payload for each vector.
For more details see the [Qdrant documentation](https://qdrant.tech/documentation/concepts/indexing/). 

### Creating an index
To create a payload index over a field:

```php
use \Mcpuishor\QdrantLaravel\Facades\Qdrant;
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
    use \Mcpuishor\QdrantLaravel\Facades\Qdrant;

    $result = Qdrant::indexes()->fulltext('text_field_name', TokenizerType::WORD);
````
It returns ``true`` if the operation was successful, or ``false`` otherwise.

### Deleting an index

````php
    use \Mcpuishor\QdrantLaravel\Facades\Qdrant;

    $result = Qdrant::indexes()->delete('payload_field');
````
It returns ``true`` if the operation was successful, or ``false`` otherwise.

## Searching
The package provides a fluent interface for searching vectors in your Qdrant collection.

### Basic Vector Search
To perform a simple search with a vector:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;

// Search using a vector
$results = Qdrant::search()
    ->vector([0.2, 0.3, 0.4, ...]) // Your vector data
    ->limit(10)
    ->get();
```

### Search by Point ID
You can also search for similar points to an existing point by its ID:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;
use Mcpuishor\QdrantLaravel\DTOs\Point;

$point = new Point(id: 123);
$results = Qdrant::search()
    ->point($point)
    ->limit(5)
    ->get();
```

### Including Payload and Vectors
Control what data is returned with your search results:

```php
// Include all payload data
$results = Qdrant::search()
    ->vector($vector)
    ->withPayload()
    ->get();

// Include only specific payload fields
$results = Qdrant::search()
    ->vector($vector)
    ->include(['name', 'description'])
    ->get();

// Exclude specific payload fields
$results = Qdrant::search()
    ->vector($vector)
    ->exclude(['internal_id'])
    ->get();

// Include vector data in results
$results = Qdrant::search()
    ->vector($vector)
    ->withVectors()
    ->get();
```

### Pagination
Control the number of results and implement pagination:

```php
// Limit results
$results = Qdrant::search()
    ->vector($vector)
    ->limit(20)
    ->get();

// Pagination with offset
$results = Qdrant::search()
    ->vector($vector)
    ->limit(10)
    ->offset(20) // Skip first 20 results
    ->get();
```

### Filtering Results
Apply filters to search results (and to `count`, `scroll`, `facet`, `discover` and `matrix`, since they
all share the same `HasFilters` trait) using `must` / `mustNot` / `should` / `minShould`, each taking a
payload key, a `FilterConditions` case, and the operand for that condition:

```php
use Mcpuishor\QdrantLaravel\Enums\FilterConditions;

// Match filter (equality) — the value is wrapped under `match.value`
$results = Qdrant::search()
    ->vector($vector)
    ->must('category', FilterConditions::MATCH, 'electronics')
    ->get();

// Range filter — the operand is passed through as-is (gte/lte/gt/lt)
$results = Qdrant::search()
    ->vector($vector)
    ->must('price', FilterConditions::RANGE, ['gte' => 100, 'lte' => 500])
    ->get();

// Combine MUST and MUST NOT
$results = Qdrant::search()
    ->vector($vector)
    ->must('category', FilterConditions::MATCH, 'electronics')
    ->mustNot('discontinued', FilterConditions::MATCH, true)
    ->get();

// SHOULD (at least one of these should match) and MIN SHOULD (at least N of these)
$results = Qdrant::search()
    ->vector($vector)
    ->should('category', FilterConditions::MATCH, 'electronics')
    ->should('category', FilterConditions::MATCH, 'gadgets')
    ->minShould('tag', FilterConditions::MATCH, 'featured', min_count: 1)
    ->get();

// is_empty / is_null only need the key, no value
$results = Qdrant::search()
    ->vector($vector)
    ->must('description', FilterConditions::IS_EMPTY)
    ->get();
```

> **Note (issue #3, fixed in 0.2.0):** filter conditions used to serialize incorrectly for several
> `FilterConditions` cases. As of 0.2.0, `match` correctly wraps its operand under `{"match": {"value": ...}}`,
> `range`/`geo_bounding_box`/`geo_polygon`/`geo_radius`/`values_count` pass their operand through unwrapped
> (e.g. `{"range": {"gte": 100}}`), and `is_empty`/`is_null` emit `{"is_empty": {"key": "..."}}` with no value
> at all. If you built filters against a version prior to 0.2.0, re-check any `range`, `is_empty`, or
> `is_null` filters — their JSON shape has changed.

### Grouping Results
Group search results by a payload field:

```php
// Group results by category
$results = Qdrant::search()
    ->vector($vector)
    ->groupBy('category', 5) // 5 results per group
    ->get();
```

### Batch Searching
Perform multiple searches in a single request:

```php
$search1 = Qdrant::search()->vector($vector1)->limit(5);
$search2 = Qdrant::search()->vector($vector2)->limit(5);

$batchResults = Qdrant::search()->batch([$search1, $search2]);
```

### Random Sampling
Get random points from the collection:

```php
$randomPoints = Qdrant::search()->random();
```

### Using Named Vectors
If your collection has multiple named vectors, specify which one to use:

```php
$results = Qdrant::search()
    ->vector($vector)
    ->using('image_embedding') // Use the named vector
    ->get();
```

## Recommendations
The package provides a recommendation system based on positive and negative examples.

### Basic Recommendations
Get recommendations based on positive examples:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;

// Recommend based on point IDs
$recommendations = Qdrant::recommend()
    ->positive([123, 456]) // Points you like
    ->limit(10)
    ->get();
```

### Positive and Negative Examples
Refine recommendations with both positive and negative examples:

```php
$recommendations = Qdrant::recommend()
    ->positive([123, 456]) // Points you like
    ->negative([789, 101]) // Points you don't like
    ->limit(10)
    ->get();
```

### Recommendation Strategy
Control how vectors are combined for recommendations:

```php
use Mcpuishor\QdrantLaravel\Enums\AverageVectorStrategy;

$recommendations = Qdrant::recommend()
    ->positive([123, 456])
    ->strategy(AverageVectorStrategy::WEIGHTED) // Use weighted average
    ->limit(10)
    ->get();
```

Available strategies include:
- `AverageVectorStrategy::MEAN` - Simple average of vectors
- `AverageVectorStrategy::WEIGHTED` - Weighted average based on similarity

## Point Operations
The package provides methods for managing points in your Qdrant collection.

### Retrieving Points
Get points by their IDs:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;

// Get multiple points
$points = Qdrant::points()->get([123, 456, 789]);

// Find a single point
$point = Qdrant::points()->find(123);
```

### Controlling Returned Data
Control what data is returned with the points:

```php
// With payload (default)
$points = Qdrant::points()->withPayload()->get([123, 456]);

// Without payload
$points = Qdrant::points()->withoutPayload()->get([123, 456]);

// With vector data
$points = Qdrant::points()->withVector()->get([123, 456]);

// Without vector data (default)
$points = Qdrant::points()->withoutVector()->get([123, 456]);
```

### Inserting Points
Insert a new point into the collection:

```php
use Mcpuishor\QdrantLaravel\DTOs\Point;

// Create a point
$point = new Point(
    id: 123,
    vector: [0.2, 0.3, 0.4, ...],
    payload: ['name' => 'Example', 'category' => 'test']
);

// Insert the point
$success = Qdrant::points()->insert($point);
```

### Upserting Points
Insert or update multiple points:

```php
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\DTOs\Point;

// Create points collection
$points = new PointsCollection([
    new Point(id: 123, vector: [0.2, 0.3, 0.4, ...], payload: ['name' => 'First']),
    new Point(id: 456, vector: [0.5, 0.6, 0.7, ...], payload: ['name' => 'Second'])
]);

// Upsert the points
$success = Qdrant::points()->upsert($points);
```

### Deleting Points
Delete points by their IDs:

```php
// Delete specific points
$success = Qdrant::points()->delete([123, 456]);

// Delete points matching a filter
$success = Qdrant::points()
    ->where('category', '=', 'test')
    ->delete([]);
```

### Autochunking
Efficiently handle large numbers of points with automatic chunking:

```php
// Create an autochunker with chunk size of 100
$chunker = Qdrant::points()->autochunk(100);

// Add points - they'll be automatically upserted when the chunk size is reached
foreach ($largeDataset as $data) {
    $point = new Point(
        id: $data['id'],
        vector: $data['embedding'],
        payload: $data['metadata']
    );
    $chunker->add($point);
}

// Manually flush any remaining points
$chunker->flush();
```

## Vector Operations
The package provides methods for managing vectors in your Qdrant collection.

### Updating Vectors
Update vectors for existing points:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Mcpuishor\QdrantLaravel\DTOs\Point;

// Create a collection of points with updated vectors
$points = new PointsCollection([
    new Point(id: 123, vector: [0.2, 0.3, 0.4, ...]),
    new Point(id: 456, vector: [0.5, 0.6, 0.7, ...])
]);

// Update the vectors
$success = Qdrant::vectors()->update($points);
```

### Deleting Vectors
Delete vectors for specific points:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;

// Delete vectors for specific points
$success = Qdrant::vectors()->delete([123, 456]);
```

## Collection Aliases
Aliases let you point a stable name at a collection and swap the underlying collection atomically —
useful for zero-downtime reindexing (build `plants_v2`, then repoint the `plants` alias to it).

Alias mutations are queued fluently and committed with a single `apply()` call:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;

// Create an alias, or several, then commit them in one atomic request
Qdrant::collection('plants')->aliases()
    ->add('plants', 'plants_v2')   // alias name, target collection
    ->delete('plants_old')
    ->apply(); // bool

// Atomically move an alias from its current target to a new collection
Qdrant::collection('plants')->aliases()
    ->switch('plants', 'plants_v2') // delete + re-add in one request
    ->apply();
```

`apply()` throws a `CommandException` if no actions have been queued.

List aliases — scoped to the current collection, or all aliases across the server:

```php
// Aliases pointing at the current collection
$aliases = Qdrant::collection('plants')->aliases()->get(); // Illuminate\Support\Collection

// All aliases on the server (no collection scope)
$all = Qdrant::aliases()->get();
```

## New in 0.2.0
Version 0.2.0 adds full coverage of the Qdrant 1.18.x REST API — counting, scrolling, batch updates,
named-vector management, facets, discovery, a distance matrix, service/health/telemetry, snapshots
(collection/storage/shard), cluster management, shard keys, and beta issues — plus the issue #3 filter fix
described above.

### Counting Points
Count points matching (optional) filters without fetching them:

```php
use Mcpuishor\QdrantLaravel\Facades\Qdrant;
use Mcpuishor\QdrantLaravel\Enums\FilterConditions;

$total = Qdrant::collection('plants')
    ->count()
    ->exact()
    ->must('category', FilterConditions::MATCH, 'tropical')
    ->get(); // int
```

### Scrolling Through Points
Page through a collection's points without vector search, optionally ordered by a payload key:

```php
$scroll = Qdrant::collection('plants')
    ->scroll()
    ->limit(50)
    ->orderBy('created_at', 'desc')
    ->withPayload()
    ->withVector();

$page = $scroll->get();               // PointsCollection
$nextOffset = $scroll->nextPageOffset(); // pass this to ->offset() on the next call
```

### Batch Updates
Combine multiple point/payload/vector operations into a single request:

```php
use Mcpuishor\QdrantLaravel\PointsCollection;

$success = Qdrant::collection('plants')
    ->batch()
    ->upsert($pointsCollection) // a PointsCollection
    ->deletePoints([1, 2, 3])
    ->setPayload(['watered' => true], points: [4, 5])
    ->clearPayload(points: [6])
    ->updateVectors($otherPointsCollection)
    ->deleteVectors(ids: [7, 8], vectorNames: ['image_embedding'])
    ->execute(); // bool
```

### Named Vectors
Add, remove, or inspect named vector configurations on an existing collection:

```php
use Mcpuishor\QdrantLaravel\DTOs\Vector;
use Mcpuishor\QdrantLaravel\Enums\DistanceMetric;

Qdrant::collection('plants')->namedVectors()->create(
    'image_embedding',
    Vector::fromArray(['size' => 512, 'distance' => DistanceMetric::COSINE])
);

Qdrant::collection('plants')->namedVectors()->delete('image_embedding');

$status = Qdrant::collection('plants')->namedVectors()->optimizations(); // array
```

### Facets
Get distinct payload values (and their counts) for a key, similar to a search facet/aggregation:

```php
$facets = Qdrant::collection('plants')
    ->facet('category')
    ->limit(20)
    ->exact()
    ->get(); // FacetResponse

foreach ($facets->hits() as $hit) {
    // ['value' => ..., 'count' => ...]
}
```

### Discovery
Find points using positive/negative context pairs plus an optional target — Qdrant's discovery search:

```php
$results = Qdrant::collection('plants')
    ->discover()
    ->target(123)
    ->context([['positive' => 456, 'negative' => 789]])
    ->using('image_embedding')
    ->limit(10)
    ->get(); // PointsCollection

// Batch discovery
$batchResults = Qdrant::collection('plants')->discover()->batch([$discover1, $discover2]);
```

### Recommendations on the Query API
`Qdrant::recommend()` is rebuilt on top of the Query API (`POST /points/query`) internally, so the
public interface is unchanged — see the [Recommendations](#recommendations) section above.

### Distance Matrix
Compute pairwise distances between a sample of points:

```php
$offsets = Qdrant::collection('plants')
    ->matrix()
    ->sample(50)
    ->limit(10)
    ->using('image_embedding')
    ->offsets(); // array

$pairs = Qdrant::collection('plants')->matrix()->sample(50)->pairs(); // array
```

### Service, Health, and Telemetry
Inspect the Qdrant server itself, independent of any collection:

```php
Qdrant::service()->root();          // array — server identity/version
Qdrant::service()->healthz();       // bool
Qdrant::service()->livez();         // bool
Qdrant::service()->readyz();        // bool
Qdrant::service()->telemetry();     // array
Qdrant::service()->metrics();       // string — Prometheus text format
```

### Collection Snapshots
Create, list, delete, and download snapshots of a collection:

```php
$snapshot = Qdrant::collection('plants')->snapshots()->create(); // SnapshotDescription

Qdrant::collection('plants')->snapshots()->list();               // Collection<SnapshotDescription>
Qdrant::collection('plants')->snapshots()->delete($snapshot->name);
Qdrant::collection('plants')->snapshots()->download($snapshot->name); // Illuminate\Http\Client\Response

// Recover from (register) a snapshot that already exists at a server-visible location
Qdrant::collection('plants')->snapshots()->recover('file:///qdrant/snapshots/plants/plants.snapshot');
```

> **Limitation:** `snapshots()->upload($path)` currently throws a `SnapshotException` — multipart upload
> of local file bytes from your application to the server is not yet implemented. Use `recover($location)`
> with a path the **Qdrant server** can already see instead.

### Storage Snapshots
Snapshot the entire storage (not tied to a single collection):

```php
$snapshot = Qdrant::storageSnapshots()->create(); // SnapshotDescription
Qdrant::storageSnapshots()->list();
Qdrant::storageSnapshots()->delete($snapshot->name);
Qdrant::storageSnapshots()->download($snapshot->name);
```

### Shard Snapshots
Snapshot an individual shard in a distributed deployment:

```php
$snapshot = Qdrant::collection('plants')->shardSnapshots(shardId: 0)->create(); // SnapshotDescription
Qdrant::collection('plants')->shardSnapshots(0)->list();
Qdrant::collection('plants')->shardSnapshots(0)->delete($snapshot->name);
Qdrant::collection('plants')->shardSnapshots(0)->download($snapshot->name);
Qdrant::collection('plants')->shardSnapshots(0)->recover('file:///qdrant/snapshots/plants/0/shard.snapshot');
```

### Cluster Management
Inspect and manage a distributed Qdrant cluster:

```php
$status = Qdrant::collection('plants')->cluster()->status(); // ClusterStatus
Qdrant::collection('plants')->cluster()->telemetry();         // array
Qdrant::collection('plants')->cluster()->recover();           // bool
Qdrant::collection('plants')->cluster()->removePeer(peerId: 4, force: false);
Qdrant::collection('plants')->cluster()->collection();        // array — this collection's cluster info
Qdrant::collection('plants')->cluster()->moveShard(shardId: 0, fromPeer: 1, toPeer: 2);
Qdrant::collection('plants')->cluster()->replicateShard(shardId: 0, fromPeer: 1, toPeer: 2);
```

### Shard Keys
Manage custom sharding for a collection:

```php
Qdrant::collection('plants')->shards()->keys();          // array
Qdrant::collection('plants')->shards()->create('region-eu');
Qdrant::collection('plants')->shards()->delete('region-eu');
```

### Issues (Beta)
Read and clear the server's self-diagnosed issues (a beta Qdrant API):

```php
Qdrant::issues()->get();   // array
Qdrant::issues()->clear(); // bool
```

## Artisan Commands

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
