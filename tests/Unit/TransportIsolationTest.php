<?php

use Illuminate\Support\Facades\Http;
use Mcpuishor\QdrantLaravel\QdrantClient;
use Mcpuishor\QdrantLaravel\QdrantTransport;

it('does not misroute a held builder when another builder is used in between', function () {
    Http::fake(['*' => Http::response(['status' => 'ok', 'time' => 0, 'result' => ['points' => [], 'next_page_offset' => null]], 200)]);
    $client = new QdrantClient(new QdrantTransport(), 'plants');

    $scroll = $client->scroll()->limit(5);   // captures /collections/plants/points
    $client->facet('category')->get();        // would mutate the shared prefix under the old code
    $scroll->get();

    Http::assertSent(fn ($req) => str_contains($req->url(), '/collections/plants/points/scroll'));
    Http::assertSent(fn ($req) => str_contains($req->url(), '/collections/plants/facet'));
});

it('gives two builders from one client independent prefixes', function () {
    Http::fake(['*' => Http::response(['status' => 'ok', 'time' => 0, 'result' => ['count' => 0]], 200)]);
    $client = new QdrantClient(new QdrantTransport(), 'plants');

    $count = $client->count();
    $client->facet('category'); // constructing another builder must not disturb $count's prefix
    $count->get();

    Http::assertSent(fn ($req) => str_contains($req->url(), '/collections/plants/points/count'));
});
