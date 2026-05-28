<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);

$dotenv->load();

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (strpos($requestPath, '/api') === 0) {
    $api = new \App\Api\ApiKernel();
    $api->send();
    exit;
}

session_start();

$route = new \App\Route();
