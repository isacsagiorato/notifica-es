<?php

namespace App\Api;

use App\Auth\AuthenticatedUser;
use App\Auth\JwtService;
use App\DAO\LoginDAO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class ApiKernel
{
    private $routes;

    public function __construct($routes = null)
    {
        $this->routes = $routes ?: require __DIR__.'/../Routes/api.php';

        if (! $this->routes instanceof RouteCollection) {
            throw new \InvalidArgumentException('As rotas da API devem ser uma instância de RouteCollection.');
        }
    }

    public function handle($requestOrMethod = null, $uri = null)
    {
        $request = ApiRequest::from($requestOrMethod, $uri);

        $context = new RequestContext;
        $context->fromRequest($request->toSymfonyRequest());

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $parameters = $matcher->match($request->path());

            $this->guard($parameters, $request);

            return $this->dispatch($parameters, $request);
        } catch (MethodNotAllowedException $exception) {
            return ApiResponse::fromHttpException(new MethodNotAllowedHttpException(
                $exception->getAllowedMethods(),
                'Método HTTP não permitido para esta rota.'
            ));
        } catch (ResourceNotFoundException $exception) {
            return ApiResponse::fromHttpException(new NotFoundHttpException('Rota da API não encontrada.'));
        } catch (HttpExceptionInterface $exception) {
            return ApiResponse::fromHttpException($exception);
        } catch (\Throwable $exception) {
            return ApiResponse::error('server_error', 'Erro interno ao processar a requisição.', 500);
        }
    }

    public function send($response = null)
    {
        $response = $response ?: $this->handle();

        (new JsonResponse($response['body'], $response['status'], [], false))->send();
    }

    private function guard(array $parameters, ApiRequest $request): void
    {
        if (($parameters['_auth'] ?? false) !== true) {
            return;
        }

        $token = $request->bearerToken();

        if ($token === null) {
            throw new UnauthorizedHttpException('Bearer', 'Token de autenticação não informado.');
        }

        try {
            $payload = JwtService::fromEnvironment()->decode($token);
            $userId = (int) ($payload['sub'] ?? 0);
            $login = $userId > 0 ? (new LoginDAO)->findById($userId) : null;

            if (! $login || $login['status'] !== 'a') {
                throw new \RuntimeException('Invalid token user.');
            }

            $request->setAuthenticatedUser(AuthenticatedUser::fromLoginRow($login));
        } catch (\Throwable $exception) {
            throw new UnauthorizedHttpException('Bearer', 'Token de autenticação inválido.');
        }
    }

    private function dispatch(array $parameters, ApiRequest $request)
    {
        $controller = $parameters['_controller'] ?? null;
        $action = $parameters['_action'] ?? null;

        unset($parameters['_controller'], $parameters['_action'], $parameters['_route']);

        if (! $controller || ! $action || ! class_exists($controller) || ! method_exists($controller, $action)) {
            return ApiResponse::error('handler_not_found', 'Handler da rota da API não encontrado.', 500);
        }

        $controllerInstance = new $controller;
        $response = $controllerInstance->$action($parameters, $request);

        return ApiResponse::normalize($response);
    }
}
