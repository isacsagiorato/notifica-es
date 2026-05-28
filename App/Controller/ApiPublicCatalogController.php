<?php

namespace App\Controller;

use App\Api\ApiResponse;
use App\DAO\PublicCatalogDAO;
use App\Http\Resources\AnimalResource;
use App\Http\Resources\BreedResource;
use App\Http\Resources\ClinicResource;
use App\Http\Resources\OngResource;
use App\Http\Resources\SpeciesResource;
use App\Http\Resources\VeterinarianResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiPublicCatalogController
{
    private const RESOURCES = [
        'animals' => AnimalResource::class,
        'species' => SpeciesResource::class,
        'breeds' => BreedResource::class,
        'ongs' => OngResource::class,
        'clinics' => ClinicResource::class,
        'veterinarians' => VeterinarianResource::class,
    ];

    public function index(array $parameters): array
    {
        $entity = $this->entityFrom($parameters);
        $resource = self::RESOURCES[$entity];
        $rows = (new PublicCatalogDAO)->list($entity);

        return ApiResponse::success($resource::collection($rows));
    }

    public function show(array $parameters): array
    {
        $entity = $this->entityFrom($parameters);
        $row = (new PublicCatalogDAO)->find($entity, (int) $parameters['id']);

        if (! $row) {
            throw new NotFoundHttpException('Recurso público não encontrado.');
        }

        $resource = self::RESOURCES[$entity];

        return ApiResponse::success(new $resource($row));
    }

    private function entityFrom(array $parameters): string
    {
        $entity = (string) ($parameters['_entity'] ?? '');

        if (! isset(self::RESOURCES[$entity])) {
            throw new NotFoundHttpException('Recurso público não encontrado.');
        }

        return $entity;
    }
}
