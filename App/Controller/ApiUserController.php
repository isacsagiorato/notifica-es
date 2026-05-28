<?php

namespace App\Controller;

use App\Api\ApiRequest;
use App\Api\ApiResponse;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiUserController
{
    public function me(array $parameters, ApiRequest $request): array
    {
        $user = $request->authenticatedUser();

        if (! $user) {
            throw new UnauthorizedHttpException('Bearer', 'Token de autenticação inválido.');
        }

        return ApiResponse::success(new UserResource($user));
    }
}
