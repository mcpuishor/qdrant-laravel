<?php

use Mcpuishor\QdrantLaravel\DTOs\Response;

it('reports ok and extracts result', function () {
    $r = new Response(['status' => 'ok', 'time' => 0.01, 'result' => ['x' => 1]]);
    expect($r->isOk())->toBeTrue()
        ->and($r->result())->toBe(['x' => 1])
        ->and($r->status())->toBe('ok')
        ->and($r->error())->toBeNull();
});

it('surfaces a server error object', function () {
    $r = new Response(['status' => ['error' => 'bad request'], 'time' => 0.0]);
    expect($r->isOk())->toBeFalse()
        ->and($r->error())->toBe('bad request');
});
