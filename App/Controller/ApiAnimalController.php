<?php

namespace App\Controller;

use App\Api\ApiRequest;
use App\Api\ApiResponse;
use App\DAO\ApiAnimalDAO;
use App\Http\Resources\AnimalResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ApiAnimalController
{
    /** Chaves da API (inglês) -> colunas da tabela animal. */
    private const FIELD_MAP = [
        'name' => 'nome',
        'birth_date' => 'data_nascimento',
        'sex' => 'sexo',
        'species' => 'especie',
        'size' => 'porte',
        'location' => 'localizacao',
        'photo' => 'foto',
        'status' => 'status',
    ];

    private const SEX = ['m', 'f'];

    private const SIZE = ['pequeno', 'medio', 'grande'];

    private const STATUS = ['disponivel', 'adotado', 'em_tratamento', 'reservado'];

    public function index(array $parameters, ApiRequest $request): array
    {
        $animals = (new ApiAnimalDAO)->filter(
            $request->query('species'),
            $request->query('status'),
        );

        return ApiResponse::success(AnimalResource::collection($animals));
    }

    public function show(array $parameters, ApiRequest $request): array
    {
        $animal = (new ApiAnimalDAO)->find((int) $parameters['id']);

        if (! $animal) {
            throw new NotFoundHttpException('Animal não encontrado.');
        }

        return ApiResponse::success(new AnimalResource($animal));
    }

    public function store(array $parameters, ApiRequest $request): array
    {
        $data = $this->mapInput($request->data());
        $data['status'] = $data['status'] ?? 'disponivel';

        $this->validate($data);

        $dao = new ApiAnimalDAO;
        $id = $dao->create($data);

        return ApiResponse::success(new AnimalResource($dao->find($id)), [], 201);
    }

    public function update(array $parameters, ApiRequest $request): array
    {
        $id = (int) $parameters['id'];
        $dao = new ApiAnimalDAO;
        $existing = $dao->find($id);

        if (! $existing) {
            throw new NotFoundHttpException('Animal não encontrado.');
        }

        // Mescla o que veio sobre o registro atual (PUT que aceita atualização parcial).
        $data = array_merge($existing, $this->mapInput($request->data()));

        $this->validate($data);

        $dao->update($id, $data);

        return ApiResponse::success(new AnimalResource($dao->find($id)));
    }

    public function destroy(array $parameters, ApiRequest $request): array
    {
        $id = (int) $parameters['id'];
        $dao = new ApiAnimalDAO;

        if (! $dao->find($id)) {
            throw new NotFoundHttpException('Animal não encontrado.');
        }

        $dao->delete($id);

        return ApiResponse::success(['id' => $id, 'deleted' => true]);
    }

    /**
     * Traduz as chaves de entrada (inglês) para as colunas da tabela,
     * mantendo apenas os campos efetivamente enviados.
     */
    private function mapInput(array $body): array
    {
        $data = [];

        foreach (self::FIELD_MAP as $input => $column) {
            if (array_key_exists($input, $body)) {
                $value = $body[$input];
                $data[$column] = is_string($value) ? trim($value) : $value;
            }
        }

        return $data;
    }

    private function validate(array $data): void
    {
        $errors = [];

        if (empty($data['nome'])) {
            $errors[] = 'O campo name é obrigatório.';
        }

        if (! empty($data['sexo']) && ! in_array($data['sexo'], self::SEX, true)) {
            $errors[] = 'O campo sex deve ser um de: '.implode(', ', self::SEX).'.';
        }

        if (! empty($data['porte']) && ! in_array($data['porte'], self::SIZE, true)) {
            $errors[] = 'O campo size deve ser um de: '.implode(', ', self::SIZE).'.';
        }

        if (! empty($data['status']) && ! in_array($data['status'], self::STATUS, true)) {
            $errors[] = 'O campo status deve ser um de: '.implode(', ', self::STATUS).'.';
        }

        if (! empty($data['data_nascimento']) && ! $this->isValidDate((string) $data['data_nascimento'])) {
            $errors[] = 'O campo birth_date deve estar no formato YYYY-MM-DD.';
        }

        if ($errors !== []) {
            throw new UnprocessableEntityHttpException(implode(' ', $errors));
        }
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
