<?php

namespace App\Api;

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
        $this->routes = $routes ?: require __DIR__ . '/../Routes/api.php';

        if (!$this->routes instanceof RouteCollection) {
            throw new \InvalidArgumentException('As rotas da API devem ser uma instância de RouteCollection.');
        }
    }

    public function handle($method = null, $uri = null)
    {
        $method = strtoupper($method ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $path = parse_url($uri ?: ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

        $context = new RequestContext();
        $context->setMethod($method);

        $matcher = new UrlMatcher($this->routes, $context);

        try {
            $parameters = $matcher->match($path);

            return $this->dispatch($parameters);
        } catch (MethodNotAllowedException $exception) {
            return ApiResponse::json([
                'error' => 'method_not_allowed',
                'message' => 'Método HTTP não permitido para esta rota.',
                'allowed_methods' => $exception->getAllowedMethods(),
            ], 405);
        } catch (ResourceNotFoundException $exception) {
            return ApiResponse::error('not_found', 'Rota da API não encontrada.', 404);
        } catch (\Throwable $exception) {
            return ApiResponse::error('server_error', 'Erro interno ao processar a requisição.', 500);
        }
    }

    public function send($response = null)
    {
        $response = $response ?: $this->handle();

        http_response_code($response['status']);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($response['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function dispatch(array $parameters)
    {
        $controller = $parameters['_controller'] ?? null;
        $action = $parameters['_action'] ?? null;

        unset($parameters['_controller'], $parameters['_action'], $parameters['_route']);

        if (!$controller || !$action || !class_exists($controller) || !method_exists($controller, $action)) {
            return ApiResponse::error('handler_not_found', 'Handler da rota da API não encontrado.', 500);
        }

        $controllerInstance = new $controller();
        $response = $controllerInstance->$action($parameters);

        return ApiResponse::normalize($response);
    }
}
