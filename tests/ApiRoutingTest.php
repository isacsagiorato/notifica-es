<?php

use App\Api\ApiKernel;
use App\Api\ApiResource;
use App\Api\ApiResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

it('returns ok for the API health route', function () {
    $response = (new ApiKernel)->handle('GET', '/api/health');

    expect($response['status'])->toBe(200)
        ->and($response['body'])->toBe([
            'data' => [
                'status' => 'ok',
            ],
            'meta' => [],
        ]);
});

it('returns method not allowed for unsupported API methods', function () {
    $response = (new ApiKernel)->handle('POST', '/api/health');

    expect($response['status'])->toBe(405)
        ->and($response['body']['error']['code'] ?? null)->toBe('method_not_allowed')
        ->and($response['body']['error']['details']['allowed_methods'] ?? [])->toBe(['GET']);
});

it('returns not found for unknown API routes', function () {
    $response = (new ApiKernel)->handle('GET', '/api/nao-existe');

    expect($response['status'])->toBe(404)
        ->and($response['body']['error'])->toBe([
            'code' => 'not_found',
            'message' => 'Rota da API não encontrada.',
            'details' => [],
        ]);
});

it('normalizes API resources into the success envelope', function () {
    $response = ApiResponse::success(new TestAnimalResource([
        'id' => 10,
        'nome' => 'Mel',
    ]));

    expect($response['status'])->toBe(200)
        ->and($response['body'])->toBe([
            'data' => [
                'id' => 10,
                'name' => 'Mel',
            ],
            'meta' => [],
        ]);
});

it('normalizes resource collections into the success envelope', function () {
    $response = ApiResponse::success(TestAnimalResource::collection([
        ['id' => 10, 'nome' => 'Mel'],
        ['id' => 11, 'nome' => 'Bob'],
    ]));

    expect($response['body'])->toBe([
        'data' => [
            ['id' => 10, 'name' => 'Mel'],
            ['id' => 11, 'name' => 'Bob'],
        ],
        'meta' => [],
    ]);
});

it('returns paginated responses with pagination metadata', function () {
    $response = ApiResponse::paginated(
        TestAnimalResource::collection([
            ['id' => 10, 'nome' => 'Mel'],
        ]),
        page: 2,
        perPage: 15,
        total: 31
    );

    expect($response['body'])->toBe([
        'data' => [
            ['id' => 10, 'name' => 'Mel'],
        ],
        'meta' => [
            'pagination' => [
                'page' => 2,
                'per_page' => 15,
                'total' => 31,
                'last_page' => 3,
            ],
        ],
    ]);
});

it('returns unauthenticated for protected routes without bearer token', function () {
    $routes = new RouteCollection;
    $routes->add('api.protected', new Route(
        path: '/api/protected',
        defaults: [
            '_controller' => TestApiController::class,
            '_action' => 'protected',
            '_auth' => true,
        ],
        methods: ['GET'],
    ));

    $response = (new ApiKernel($routes))->handle(Request::create('/api/protected', 'GET'));

    expect($response['status'])->toBe(401)
        ->and($response['body']['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'Token de autenticação não informado.',
            'details' => [],
        ]);
});

it('maps Symfony HTTP exceptions to the error envelope', function () {
    $routes = new RouteCollection;
    $routes->add('api.forbidden', new Route(
        path: '/api/forbidden',
        defaults: [
            '_controller' => TestApiController::class,
            '_action' => 'forbidden',
        ],
        methods: ['GET'],
    ));

    $response = (new ApiKernel($routes))->handle(Request::create('/api/forbidden', 'GET'));

    expect($response['status'])->toBe(403)
        ->and($response['body']['error'])->toBe([
            'code' => 'forbidden',
            'message' => 'Sem permissão.',
            'details' => [],
        ]);
});

class TestAnimalResource extends ApiResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['nome'],
        ];
    }
}

class TestApiController
{
    public function protected(): array
    {
        return ApiResponse::success(['ok' => true]);
    }

    public function forbidden(): void
    {
        throw new AccessDeniedHttpException('Sem permissão.');
    }
}
