<?php

declare(strict_types=1);

namespace App\Services;

use App\Attributes\Route;
use League\Route\Router;
use ReflectionClass;
use ReflectionMethod;
use DI\Container;

class RouteDiscovery
{
    public function __construct(
        private Router $router,
        private Container $container
    ) {
    }

    public function discoverRoutes(array $controllerClasses): void
    {
        foreach ($controllerClasses as $controllerClass) {
            $this->registerRoutesForController($controllerClass);
        }
    }

    private function registerRoutesForController(string $controllerClass): void
    {
        $reflectionClass = new ReflectionClass($controllerClass);
        
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Route::class);
            
            foreach ($attributes as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();
                
                $this->router->map(
                    strtoupper($route->method),
                    $route->path,
                    [$controllerClass, $method->getName()]
                );
            }
        }
    }
}
