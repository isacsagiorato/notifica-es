<?php

namespace App\Controller;

use App\Api\ApiRequest;
use App\Api\ApiResponse;
use App\Auth\AuthenticatedUser;
use App\Auth\JwtService;
use App\DAO\LoginDAO;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiAuthController
{
    public function login(array $parameters, ApiRequest $request): array
    {
        $data = $request->data();
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '') {
            throw new BadRequestHttpException('E-mail e senha são obrigatórios.');
        }

        $loginDAO = new LoginDAO;
        $login = $loginDAO->findByEmail($email);

        if (! $login || $login['status'] !== 'a' || ! password_verify($password, $login['senha'])) {
            throw new UnauthorizedHttpException('Bearer', 'Credenciais inválidas.');
        }

        if (password_needs_rehash($login['senha'], PASSWORD_DEFAULT)) {
            $loginDAO->updatePasswordHash((int) $login['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        $user = AuthenticatedUser::fromLoginRow($login);
        $jwt = JwtService::fromEnvironment();

        return ApiResponse::success([
            'token' => $jwt->issue($user),
            'token_type' => 'Bearer',
            'expires_in' => $jwt->ttlSeconds(),
            'user' => new UserResource($user),
        ]);
    }
}
