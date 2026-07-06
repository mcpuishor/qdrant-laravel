# Changelog

All notable changes to `mcpuishor/qdrant-laravel` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
Versions are published as git tags on Packagist (there is no `version` field in `composer.json`).

## [0.2.0] - 2026-07-07

Full coverage of the Qdrant 1.18.x REST API.

### Added
- `count()` — count points matching a filter (`POST /points/count`), with `->exact()`.
- `scroll()` — paginate through a collection's points without vector search, with `->limit()`,
  `->offset()`, `->orderBy()`, `->withPayload()`, `->withVector()`, and `->nextPageOffset()`
  (`POST /points/scroll`).
- `batch()` — combine multiple point/payload/vector operations into a single request
  (`POST /points/batch`): `upsert`, `deletePoints`, `setPayload`, `overwritePayload`,
  `deletePayload`, `clearPayload`, `updateVectors`, `deleteVectors`.
- `namedVectors()` — create/delete named vector configurations on an existing collection, and read
  `->optimizations()` status.
- `facet()` — distinct payload values and counts for a key (`POST /facet`), with `->limit()`,
  `->exact()`, and filters; returns a `FacetResponse` (`->hits()`).
- `discover()` — discovery search using target/context pairs (`POST /points/discover`), plus
  `->batch()` (`POST /points/discover/batch`).
- `recommend()` — rebuilt on top of the Query API (`POST /points/query`) instead of the legacy
  recommend endpoint; public interface (`positive`/`negative`/`strategy`/`get`) is unchanged.
- `matrix()` — pairwise distance matrix for a sample of points (`POST /points/search/matrix/offsets`
  and `/pairs`), with `->sample()`, `->limit()`, `->using()`.
- `service()` — server-level `root()`, `healthz()`, `livez()`, `readyz()`, `telemetry()`, `metrics()`.
- `snapshots()` — collection snapshots: `create()`, `list()`, `delete()`, `download()`, `recover()`,
  `upload()` (see note below), returning `SnapshotDescription`.
- `storageSnapshots()` — whole-storage snapshots (not tied to a collection): `create()`, `list()`,
  `delete()`, `download()`.
- `shardSnapshots($shardId)` — per-shard snapshots: `create()`, `list()`, `delete()`, `download()`,
  `recover()`.
- `cluster()` — distributed cluster management: `status()` (returns `ClusterStatus`), `telemetry()`,
  `recover()`, `removePeer()`, `collection()`, `moveShard()`, `replicateShard()`.
- `shards()` — custom shard key management: `keys()`, `create()`, `delete()`.
- `issues()` — beta server self-diagnostics: `get()`, `clear()`.

### Fixed
- **Filter building (issue #3):** `must` / `mustNot` / `should` / `minShould` now emit the correct
  Qdrant filter clause shapes. `match` wraps its operand under `{"match": {"value": ...}}`;
  `range` / `geo_bounding_box` / `geo_polygon` / `geo_radius` / `values_count` pass their operand
  through unwrapped (e.g. `{"range": {"gte": 100}}`); `is_empty` / `is_null` emit `{"key": "..."}`
  only, with no value. Previously these clauses were serialized incorrectly for every condition
  other than `match`.
- **Response DTO hardening:** `QdrantTransport::post()`/`get()` were constructing the `Response` DTO
  with two constructor arguments against a single-argument constructor; calls now match the actual
  `Response(array $serverResponse)` signature.
- **Transport double-decoding:** `QdrantTransport::delete()`/`patch()` were calling `json_decode()`
  on a value that Laravel's HTTP client had already decoded into an array, which could raise a
  `TypeError`; responses are now normalized once, consistently, across all HTTP verbs.
- **Alias payload shape:** `Schema\Alias::apply()` wrapped its actions under a spurious `"json"` key
  instead of sending `{"actions": [...]}` directly, which Qdrant rejected.

### Known limitations
- `snapshots()->upload()` throws until multipart is implemented. Use `recover($location)` with a
  location the **Qdrant server** can already see instead.
- The legacy `POST /points/search`, `/points/search/batch`, and `/points/search/groups` endpoints are
  not wrapped separately; they are superseded by the Query API (`Query/Search`) already in use by
  `search()` and `recommend()`.

## [0.1.0]

First tagged release: collection schema management, indexes, points/vectors/payload operations,
fluent search (vector/point/filter/group-by/batch/random/named-vector), recommendations, the
`qdrant:migrate` artisan command, and Macroable extension points.
