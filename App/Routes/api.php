<?php

use App\Controller\ApiAuthController;
use App\Controller\ApiHealthController;
use App\Controller\ApiPublicCatalogController;
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

foreach (['animals', 'species', 'breeds', 'ongs', 'clinics', 'veterinarians'] as $entity) {
    $routes->add("api.{$entity}.index", new Route(
        path: "/api/{$entity}",
        defaults: [
            '_controller' => ApiPublicCatalogController::class,
            '_action' => 'index',
            '_entity' => $entity,
        ],
        methods: ['GET'],
    ));

    $routes->add("api.{$entity}.show", new Route(
        path: "/api/{$entity}/{id}",
        defaults: [
            '_controller' => ApiPublicCatalogController::class,
            '_action' => 'show',
            '_entity' => $entity,
        ],
        requirements: [
            'id' => '\d+',
        ],
        methods: ['GET'],
    ));
}

return $routes;
