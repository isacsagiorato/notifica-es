<?php

use App\Api\ApiKernel;
use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

beforeEach(function () {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

    $this->pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8', $_ENV['DB_HOST'], $_ENV['DB_NAME']),
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    $this->pdo->exec('TRUNCATE TABLE login');
    $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
});

it('authenticates active users and returns a JWT with user data', function () {
    createApiUser($this->pdo, 'adotante@example.com', 'secret');

    $response = (new ApiKernel)->handle(jsonRequest('/api/auth/login', [
        'email' => 'adotante@example.com',
        'password' => 'secret',
    ]));

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['token'])->toBeString()->not->toBeEmpty()
        ->and($response['body']['data']['token_type'])->toBe('Bearer')
        ->and($response['body']['data']['expires_in'])->toBe(3600)
        ->and($response['body']['data']['user'])->toMatchArray([
            'email' => 'adotante@example.com',
            'type' => 'adotante',
            'status' => 'a',
        ])
        ->and($response['body']['data']['user'])->not->toHaveKey('password')
        ->and($response['body']['meta'])->toBe([]);
});

it('rejects invalid login credentials', function () {
    createApiUser($this->pdo, 'adotante@example.com', 'secret');

    $response = (new ApiKernel)->handle(jsonRequest('/api/auth/login', [
        'email' => 'adotante@example.com',
        'password' => 'wrong',
    ]));

    expect($response['status'])->toBe(401)
        ->and($response['body']['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'Credenciais inválidas.',
            'details' => [],
        ]);
});

it('rejects login requests without required credentials', function () {
    $response = (new ApiKernel)->handle(jsonRequest('/api/auth/login', [
        'email' => 'adotante@example.com',
    ]));

    expect($response['status'])->toBe(400)
        ->and($response['body']['error'])->toBe([
            'code' => 'bad_request',
            'message' => 'E-mail e senha são obrigatórios.',
            'details' => [],
        ]);
});

it('rejects inactive users during login', function () {
    createApiUser($this->pdo, 'adotante@example.com', 'secret', 'i');

    $response = (new ApiKernel)->handle(jsonRequest('/api/auth/login', [
        'email' => 'adotante@example.com',
        'password' => 'secret',
    ]));

    expect($response['status'])->toBe(401)
        ->and($response['body']['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'Credenciais inválidas.',
            'details' => [],
        ]);
});

it('returns method not allowed for unsupported login methods', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/auth/login', 'GET'));

    expect($response['status'])->toBe(405)
        ->and($response['body']['error']['code'] ?? null)->toBe('method_not_allowed')
        ->and($response['body']['error']['details']['allowed_methods'] ?? [])->toBe(['POST']);
});

it('returns the authenticated user for users me', function () {
    createApiUser($this->pdo, 'adotante@example.com', 'secret');

    $login = (new ApiKernel)->handle(jsonRequest('/api/auth/login', [
        'email' => 'adotante@example.com',
        'password' => 'secret',
    ]));

    $response = (new ApiKernel)->handle(Request::create(
        '/api/users/me',
        'GET',
        server: ['HTTP_AUTHORIZATION' => 'Bearer '.$login['body']['data']['token']]
    ));

    expect($response['status'])->toBe(200)
        ->and($response['body'])->toBe([
            'data' => [
                'id' => $login['body']['data']['user']['id'],
                'email' => 'adotante@example.com',
                'type' => 'adotante',
                'status' => 'a',
            ],
            'meta' => [],
        ]);
});

it('rejects users me without bearer token', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/users/me', 'GET'));

    expect($response['status'])->toBe(401)
        ->and($response['body']['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'Token de autenticação não informado.',
            'details' => [],
        ]);
});

it('rejects protected routes with invalid bearer tokens', function () {
    $response = (new ApiKernel)->handle(Request::create(
        '/api/users/me',
        'GET',
        server: ['HTTP_AUTHORIZATION' => 'Bearer invalid-token']
    ));

    expect($response['status'])->toBe(401)
        ->and($response['body']['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'Token de autenticação inválido.',
            'details' => [],
        ]);
});

it('returns method not allowed for unsupported users me methods', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/users/me', 'POST'));

    expect($response['status'])->toBe(405)
        ->and($response['body']['error']['code'] ?? null)->toBe('method_not_allowed')
        ->and($response['body']['error']['details']['allowed_methods'] ?? [])->toBe(['GET']);
});

function jsonRequest(string $path, array $payload): Request
{
    return Request::create(
        $path,
        'POST',
        server: ['CONTENT_TYPE' => 'application/json'],
        content: json_encode($payload, JSON_THROW_ON_ERROR)
    );
}

function createApiUser(PDO $pdo, string $email, string $password, string $status = 'a'): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO login (tipo_usuario, email, senha, status) VALUES (:type, :email, :password, :status)'
    );

    $stmt->execute([
        'type' => 'adotante',
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'status' => $status,
    ]);

    return (int) $pdo->lastInsertId();
}
