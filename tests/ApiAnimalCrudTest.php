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

    foreach (['solicitacao_adocao', 'animal', 'login'] as $table) {
        $this->pdo->exec("TRUNCATE TABLE {$table}");
    }

    $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    seedAnimals($this->pdo);
});

// ----- Leitura (pública) -----

it('lists animals without authentication', function () {
    $response = (new ApiKernel)->handle('GET', '/api/animals');

    expect($response['status'])->toBe(200)
        ->and(array_column($response['body']['data'], 'id'))->toBe([1, 2, 3]);
});

it('filters animals by species', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/animals', 'GET', ['species' => 'Gato']));

    expect($response['status'])->toBe(200)
        ->and(array_column($response['body']['data'], 'name'))->toBe(['Felix']);
});

it('filters animals by status', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/animals', 'GET', ['status' => 'adotado']));

    expect($response['status'])->toBe(200)
        ->and(array_column($response['body']['data'], 'name'))->toBe(['Thor']);
});

it('filters animals by species and status combined', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/animals', 'GET', [
        'species' => 'Cachorro',
        'status' => 'disponivel',
    ]));

    expect($response['status'])->toBe(200)
        ->and(array_column($response['body']['data'], 'name'))->toBe(['Rex']);
});

it('shows an animal profile by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/animals/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['name'])->toBe('Rex')
        ->and($response['body']['data'])->not->toHaveKey('adoption_requests');
});

it('returns not found for a missing animal', function () {
    $response = (new ApiKernel)->handle('GET', '/api/animals/999');

    expect($response['status'])->toBe(404)
        ->and($response['body']['error'])->toBe([
            'code' => 'not_found',
            'message' => 'Animal não encontrado.',
            'details' => [],
        ]);
});

// ----- Escrita (protegida) -----

it('rejects creating an animal without a token', function () {
    $response = (new ApiKernel)->handle(jsonRequest('/api/animals', [
        'name' => 'Novo',
    ]));

    expect($response['status'])->toBe(401)
        ->and($response['body']['error']['code'])->toBe('unauthenticated');
});

it('creates an animal when authenticated', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals', 'POST', [
        'name' => 'Luna',
        'birth_date' => '2025-02-01',
        'sex' => 'f',
        'species' => 'Gato',
        'size' => 'pequeno',
        'location' => 'Campinas',
        'photo' => 'luna.jpg',
        'status' => 'disponivel',
    ], $token));

    expect($response['status'])->toBe(201)
        ->and($response['body']['data']['name'])->toBe('Luna')
        ->and($response['body']['data']['species'])->toBe('Gato');

    $count = $this->pdo->query("SELECT COUNT(*) FROM animal WHERE nome = 'Luna'")->fetchColumn();
    expect((int) $count)->toBe(1);
});

it('defaults status to disponivel on create', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals', 'POST', [
        'name' => 'SemStatus',
    ], $token));

    expect($response['status'])->toBe(201)
        ->and($response['body']['data']['status'])->toBe('disponivel');
});

it('validates required fields on create', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals', 'POST', [
        'species' => 'Gato',
    ], $token));

    expect($response['status'])->toBe(422)
        ->and($response['body']['error']['code'])->toBe('validation_error');
});

it('validates enum fields on create', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals', 'POST', [
        'name' => 'Bug',
        'sex' => 'x',
    ], $token));

    expect($response['status'])->toBe(422)
        ->and($response['body']['error']['code'])->toBe('validation_error');
});

it('updates an animal when authenticated', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals/1', 'PUT', [
        'status' => 'adotado',
        'location' => 'Santos',
    ], $token));

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['status'])->toBe('adotado')
        ->and($response['body']['data']['location'])->toBe('Santos')
        // mantém o que não foi enviado
        ->and($response['body']['data']['name'])->toBe('Rex');
});

it('returns not found when updating a missing animal', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals/999', 'PUT', [
        'name' => 'Fantasma',
    ], $token));

    expect($response['status'])->toBe(404);
});

it('deletes an animal when authenticated', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals/2', 'DELETE', [], $token));

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe(['id' => 2, 'deleted' => true]);

    $count = $this->pdo->query('SELECT COUNT(*) FROM animal WHERE id = 2')->fetchColumn();
    expect((int) $count)->toBe(0);
});

it('returns not found when deleting a missing animal', function () {
    $token = animalAuthToken($this->pdo);

    $response = (new ApiKernel)->handle(authedJsonRequest('/api/animals/999', 'DELETE', [], $token));

    expect($response['status'])->toBe(404);
});

function seedAnimals(PDO $pdo): void
{
    $pdo->exec("INSERT INTO animal (id, nome, data_nascimento, sexo, especie, porte, localizacao, foto, status) VALUES
        (1, 'Rex', '2023-06-10', 'm', 'Cachorro', 'grande', 'São Paulo', 'rex.jpg', 'disponivel'),
        (2, 'Felix', '2024-01-15', 'm', 'Gato', 'pequeno', 'São Paulo', 'felix.jpg', 'disponivel'),
        (3, 'Thor', '2022-03-20', 'm', 'Cachorro', 'medio', 'São Paulo', 'thor.jpg', 'adotado')");
}

function animalAuthToken(PDO $pdo): string
{
    createApiUser($pdo, 'crud@example.com', 'secret');

    $login = (new ApiKernel)->handle(jsonRequest('/api/auth/login', [
        'email' => 'crud@example.com',
        'password' => 'secret',
    ]));

    return $login['body']['data']['token'];
}

function authedJsonRequest(string $path, string $method, array $payload, string $token): Request
{
    return Request::create(
        $path,
        $method,
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ],
        content: json_encode($payload, JSON_THROW_ON_ERROR)
    );
}
