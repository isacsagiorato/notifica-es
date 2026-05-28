<?php

use App\Controller\ApiAuthController;
use App\Controller\ApiHealthController;
use App\Controller\ApiUserController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection;

$routes->add('api.health', new Route(
    path: '/api/health',
    defaults: [
        '_controller' => ApiHealthController::class,
        '_action' => 'health',
    ],
    methods: ['GET'],
));

$routes->add('api.auth.login', new Route(
    path: '/api/auth/login',
    defaults: [
        '_controller' => ApiAuthController::class,
        '_action' => 'login',
    ],
    methods: ['POST'],
));

$routes->add('api.users.me', new Route(
    path: '/api/users/me',
    defaults: [
        '_controller' => ApiUserController::class,
        '_action' => 'me',
        '_auth' => true,
    ],
    methods: ['GET'],
));

return $routes;
