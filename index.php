<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Container\ContainerConfig;
use App\Services\RouteDiscovery;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;

try {
    // Create DI Container
    $container = ContainerConfig::create();

    // Get router from container
    $router = $container->get(Router::class);

    // Set up strategy to use DI container
    $strategy = new ApplicationStrategy();
    $strategy->setContainer($container);
    $router->setStrategy($strategy);

    // Automatically discover controllers and register routes from attributes
    $routeDiscovery = $container->get(RouteDiscovery::class);
    $routeDiscovery->discoverRoutes([
        __DIR__ . '/src/Controllers'
    ]);

    // Create server request
    $request = ServerRequestFactory::fromGlobals();

    // Dispatch the request
    $response = $router->dispatch($request);

    // Emit the response
    (new SapiEmitter())->emit($response);

} catch (Throwable $e) {
    // Simple error handling
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
