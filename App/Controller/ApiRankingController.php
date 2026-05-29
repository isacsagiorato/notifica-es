<?php

namespace App\Controller;

use App\Api\ApiRequest;
use App\Api\ApiResponse;
use App\DAO\RankingDAO;
use App\Http\Resources\AnimalResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApiRankingController
{
    /**
     * Filtros de ordenação suportados.
     * 'visualizados' ainda não é possível: não há coluna de visualizações no schema.
     */
    private const ORDERS = ['adotados'];

    public function index(array $parameters, ApiRequest $request): array
    {
        $order = (string) ($request->query('order') ?? 'adotados');

        if (! in_array($order, self::ORDERS, true)) {
            throw new BadRequestHttpException(
                "Filtro de ranking inválido: '{$order}'. Disponível: adotados."
            );
        }

        $animals = (new RankingDAO)->mostAdopted();

        return ApiResponse::success(AnimalResource::collection($animals));
    }
}
