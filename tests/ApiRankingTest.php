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

    foreach (['solicitacao_adocao', 'animal'] as $table) {
        $this->pdo->exec("TRUNCATE TABLE {$table}");
    }

    $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    seedRankingData($this->pdo);
});

it('ranks animals by number of adoption requests, descending', function () {
    $response = (new ApiKernel)->handle('GET', '/api/ranking');

    expect($response['status'])->toBe(200);

    $data = $response['body']['data'];

    expect(array_column($data, 'id'))->toBe([2, 1, 3])
        ->and(array_column($data, 'adoption_requests'))->toBe([3, 1, 0]);
});

it('includes the standard animal fields plus adoption_requests', function () {
    $response = (new ApiKernel)->handle('GET', '/api/ranking');

    expect($response['body']['data'][0])->toBe([
        'id' => 2,
        'name' => 'Mel',
        'birth_date' => '2024-01-15',
        'sex' => 'f',
        'species' => 'Cachorro',
        'size' => 'pequeno',
        'location' => 'São Paulo',
        'photo' => 'mel.jpg',
        'status' => 'disponivel',
        'adoption_requests' => 3,
    ])
        ->and($response['body']['meta'])->toBe([]);
});

it('does not require authentication', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/ranking', 'GET'));

    expect($response['status'])->toBe(200);
});

it('rejects an unsupported order filter with a 400 error', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/ranking', 'GET', ['order' => 'visualizados']));

    expect($response['status'])->toBe(400)
        ->and($response['body']['error']['code'])->toBe('bad_request');
});

it('returns method not allowed for unsupported methods', function () {
    $response = (new ApiKernel)->handle('POST', '/api/ranking');

    expect($response['status'])->toBe(405)
        ->and($response['body']['error']['code'])->toBe('method_not_allowed');
});

function seedRankingData(PDO $pdo): void
{
    $pdo->exec("INSERT INTO animal (id, nome, data_nascimento, sexo, especie, porte, localizacao, foto, status) VALUES
        (1, 'Rex', '2023-06-10', 'm', 'Cachorro', 'grande', 'São Paulo', 'rex.jpg', 'disponivel'),
        (2, 'Mel', '2024-01-15', 'f', 'Cachorro', 'pequeno', 'São Paulo', 'mel.jpg', 'disponivel'),
        (3, 'Thor', '2022-03-20', 'm', 'Cachorro', 'medio', 'São Paulo', 'thor.jpg', 'disponivel')");

    // Mel (id 2) recebe 3 solicitações, Rex (id 1) recebe 1, Thor (id 3) nenhuma.
    $pdo->exec("INSERT INTO solicitacao_adocao (data, status, motivo, fk_adotante_id, fk_animal_id) VALUES
        (NOW(), 'a', NULL, NULL, 2),
        (NOW(), 'a', NULL, NULL, 2),
        (NOW(), 'p', NULL, NULL, 2),
        (NOW(), 'a', NULL, NULL, 1)");
}
