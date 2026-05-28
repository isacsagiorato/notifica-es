<?php

use App\Api\ApiKernel;

it('returns ok for the API health route', function () {
    $response = (new ApiKernel())->handle('GET', '/api/health');

    expect($response['status'])->toBe(200)
        ->and($response['body']['status'] ?? null)->toBe('ok');
});

it('returns method not allowed for unsupported API methods', function () {
    $response = (new ApiKernel())->handle('POST', '/api/health');

    expect($response['status'])->toBe(405)
        ->and($response['body']['error'] ?? null)->toBe('method_not_allowed');
});

it('returns not found for unknown API routes', function () {
    $response = (new ApiKernel())->handle('GET', '/api/nao-existe');

    expect($response['status'])->toBe(404)
        ->and($response['body']['error'] ?? null)->toBe('not_found');
});
