<?php

use App\Controller\ApiHealthController;
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

return $routes;
