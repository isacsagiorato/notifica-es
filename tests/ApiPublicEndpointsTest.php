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

    foreach ([
        'animal_raca',
        'raca',
        'especie',
        'ong_animal',
        'historico_animal',
        'solicitacao_adocao',
        'animal',
        'ong',
        'clinica',
        'veterinario',
    ] as $table) {
        $this->pdo->exec("TRUNCATE TABLE {$table}");
    }

    $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    seedPublicApiData($this->pdo);
});

it('lists public animals', function () {
    $response = (new ApiKernel)->handle('GET', '/api/animals');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            [
                'id' => 1,
                'name' => 'Mel',
                'birth_date' => '2024-01-15',
                'sex' => 'f',
                'species' => 'Cachorro',
                'size' => 'pequeno',
                'location' => 'São Paulo',
                'photo' => 'mel.jpg',
                'status' => 'disponivel',
            ],
        ])
        ->and($response['body']['meta'])->toBe([]);
});

it('shows a public animal by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/animals/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['name'])->toBe('Mel')
        ->and($response['body']['data']['status'])->toBe('disponivel');
});

it('lists public species', function () {
    $response = (new ApiKernel)->handle('GET', '/api/species');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            ['id' => 1, 'name' => 'Cachorro'],
        ]);
});

it('shows a public species by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/species/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            'id' => 1,
            'name' => 'Cachorro',
        ]);
});

it('lists public breeds', function () {
    $response = (new ApiKernel)->handle('GET', '/api/breeds');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            ['id' => 1, 'name' => 'Vira-lata', 'species_id' => 1],
        ]);
});

it('shows a public breed by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/breeds/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            'id' => 1,
            'name' => 'Vira-lata',
            'species_id' => 1,
        ]);
});

it('lists public ongs', function () {
    $response = (new ApiKernel)->handle('GET', '/api/ongs');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            [
                'id' => 1,
                'name' => 'ONG Patinhas',
                'cnpj' => '12.345.678/0001-90',
                'status' => 'a',
                'animal_count' => 12,
                'phone_1' => '(11) 99999-0000',
                'phone_2' => null,
                'city' => 'São Paulo',
                'state' => 'SP',
                'neighborhood' => 'Centro',
                'street' => 'Rua A',
                'number' => 100,
                'complement' => null,
                'zip_code' => '01000-000',
            ],
        ]);
});

it('shows a public ong by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/ongs/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['name'])->toBe('ONG Patinhas');
});

it('lists public clinics', function () {
    $response = (new ApiKernel)->handle('GET', '/api/clinics');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            [
                'id' => 1,
                'name' => 'Clínica Central',
                'cnpj' => '98.765.432/0001-10',
                'phone_1' => '(11) 3333-0000',
                'phone_2' => null,
                'city' => 'São Paulo',
                'state' => 'SP',
                'neighborhood' => 'Centro',
                'street' => 'Rua B',
                'number' => 200,
                'complement' => null,
                'zip_code' => '02000-000',
            ],
        ]);
});

it('shows a public clinic by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/clinics/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['name'])->toBe('Clínica Central');
});

it('lists public veterinarians', function () {
    $response = (new ApiKernel)->handle('GET', '/api/veterinarians');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data'])->toBe([
            [
                'id' => 1,
                'name' => 'Dra. Ana',
                'crmv' => '12345678',
                'phone' => '(11) 98888-0000',
                'phone_2' => null,
                'city' => 'São Paulo',
                'state' => 'SP',
                'neighborhood' => 'Centro',
                'street' => 'Rua C',
                'number' => 300,
                'complement' => null,
                'zip_code' => '03000-000',
            ],
        ]);
});

it('shows a public veterinarian by id', function () {
    $response = (new ApiKernel)->handle('GET', '/api/veterinarians/1');

    expect($response['status'])->toBe(200)
        ->and($response['body']['data']['name'])->toBe('Dra. Ana');
});

it('returns not found for missing public resources', function () {
    $response = (new ApiKernel)->handle('GET', '/api/species/999');

    expect($response['status'])->toBe(404)
        ->and($response['body']['error'])->toBe([
            'code' => 'not_found',
            'message' => 'Recurso público não encontrado.',
            'details' => [],
        ]);
});

it('does not require authentication for public endpoints', function () {
    $response = (new ApiKernel)->handle(Request::create('/api/animals', 'GET'));

    expect($response['status'])->toBe(200);
});

function seedPublicApiData(PDO $pdo): void
{
    $pdo->exec("INSERT INTO especie (id, nome) VALUES (1, 'Cachorro')");
    $pdo->exec("INSERT INTO raca (id, nome, fk_especie_id) VALUES (1, 'Vira-lata', 1)");
    $pdo->exec("INSERT INTO animal (id, nome, data_nascimento, sexo, especie, porte, localizacao, foto, status) VALUES (1, 'Mel', '2024-01-15', 'f', 'Cachorro', 'pequeno', 'São Paulo', 'mel.jpg', 'disponivel')");
    $pdo->exec("INSERT INTO ong (id, cep, cnpj, status, quantidade_animais, nome, telefone_1, telefone_2, bairro, estado, complemento, logradouro, cidade, numero) VALUES (1, '01000-000', '12.345.678/0001-90', 'a', 12, 'ONG Patinhas', '(11) 99999-0000', NULL, 'Centro', 'SP', NULL, 'Rua A', 'São Paulo', 100)");
    $pdo->exec("INSERT INTO clinica (id, nome, cnpj, cep, telefone_1, logradouro, numero, bairro, cidade, estado, complemento, telefone_2) VALUES (1, 'Clínica Central', '98.765.432/0001-10', '02000-000', '(11) 3333-0000', 'Rua B', 200, 'Centro', 'São Paulo', 'SP', NULL, NULL)");
    $pdo->exec("INSERT INTO veterinario (id, nome, crmv, data_nascimento, cpf, telefone, cep, logradouro, cidade, telefone_2, numero, bairro, complemento, estado) VALUES (1, 'Dra. Ana', '12345678', '1988-05-10', '123.456.789-00', '(11) 98888-0000', '03000-000', 'Rua C', 'São Paulo', NULL, 300, 'Centro', NULL, 'SP')");
}
