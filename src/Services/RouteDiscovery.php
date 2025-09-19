<?php

declare(strict_types=1);

namespace App\Services;

use App\Attributes\Middleware;
use App\Attributes\MiddlewareGroup;
use App\Attributes\Route;
use App\Middleware\LoggingMiddleware;
use App\Middleware\LoggingMiddleware2;
use App\Middleware\LoggingMiddleware3;
use App\Middleware\LoggingMiddleware4;
use DirectoryIterator;
use League\Route\Router;
use League\Route\Route as LeagueRoute;
use ReflectionClass;
use ReflectionMethod;
use DI\Container;

class RouteDiscovery
{
    /**
     * Predefined middleware groups
     * @var array<string, array<string>>
     */
    private array $middlewareGroups = [
        'group_1' => [
            LoggingMiddleware::class,
            LoggingMiddleware2::class,
        ],
        'group_2' => [
            LoggingMiddleware3::class,
            LoggingMiddleware4::class,
        ],
    ];

    public function __construct(
        private Router $router,
        private Container $container
    ) {
    }

    /**
     * Discover controllers in given directories and register their routes
     * 
     * @param array<string> $directories Array of directory paths to scan for controllers
     */
    public function discoverRoutes(array $directories): void
    {
        foreach ($directories as $directory) {
            $controllers = $this->discoverControllersInDirectory($directory);
            
            foreach ($controllers as $controllerClass) {
                $this->registerRoutesForController($controllerClass);
            }
        }
    }

    /**
     * Discover all controller classes in a given directory
     * 
     * @param string $directory Path to the directory to scan
     * @return array<string> Array of fully qualified controller class names
     */
    private function discoverControllersInDirectory(string $directory): array
    {
        $controllers = [];
        
        if (!is_dir($directory)) {
            return $controllers;
        }

        $iterator = new DirectoryIterator($directory);
        
        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            // Convert file path to namespace
            $className = $this->pathToNamespace($directory, $file->getBasename('.php'));
            
            // Verify the class exists and is instantiable
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                
                // Only include concrete classes (not abstract or interfaces)
                if (!$reflection->isAbstract() && !$reflection->isInterface()) {
                    $controllers[] = $className;
                }
            }
        }

        return $controllers;
    }

    /**
     * Convert directory path and filename to fully qualified class name
     */
    private function pathToNamespace(string $directory, string $className): string
    {
        // Normalize the directory path
        $normalizedDir = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $directory);
        
        // Default mapping for src/Controllers
        if (str_contains($normalizedDir, 'Controllers')) {
            return 'App\\Controllers\\' . $className;
        }
        
        // You can add more namespace mappings here if needed
        return 'App\\' . $className;
    }

    private function registerRoutesForController(string $controllerClass): void
    {
        $reflectionClass = new ReflectionClass($controllerClass);
        
        // Get class-level middleware
        $classMiddleware = $this->getMiddlewareFromAttributes($reflectionClass->getAttributes(Middleware::class));
        
        // Get class-level middleware groups
        $classMiddlewareGroups = $this->getMiddlewareFromGroupAttributes($reflectionClass->getAttributes(MiddlewareGroup::class));
        
        // Combine class middleware and middleware groups
        $classMiddleware = array_merge($classMiddleware, $classMiddlewareGroups);
        
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttributes = $method->getAttributes(Route::class);
            
            foreach ($routeAttributes as $routeAttribute) {
                /** @var Route $route */
                $route = $routeAttribute->newInstance();
                
                // Get method-level middleware
                $methodMiddleware = $this->getMiddlewareFromAttributes($method->getAttributes(Middleware::class));
                
                // Get method-level middleware groups
                $methodMiddlewareGroups = $this->getMiddlewareFromGroupAttributes($method->getAttributes(MiddlewareGroup::class));
                
                // Combine method middleware and middleware groups
                $methodMiddleware = array_merge($methodMiddleware, $methodMiddlewareGroups);
                
                // Combine class and method middleware (class middleware first)
                $allMiddleware = array_merge($classMiddleware, $methodMiddleware);
                
                // Register the route
                $leagueRoute = $this->router->map(
                    strtoupper($route->method),
                    $route->path,
                    [$controllerClass, $method->getName()]
                );
                
                // Add middleware to the route
                foreach ($allMiddleware as $middlewareClass) {
                    $leagueRoute->middleware($this->container->get($middlewareClass));
                }
            }
        }
    }

    /**
     * Extract middleware classes from middleware attributes
     * 
     * @param array $middlewareAttributes
     * @return array<string>
     */
    private function getMiddlewareFromAttributes(array $middlewareAttributes): array
    {
        $middleware = [];
        
        foreach ($middlewareAttributes as $attribute) {
            /** @var Middleware $middlewareAttr */
            $middlewareAttr = $attribute->newInstance();
            $middleware = array_merge($middleware, $middlewareAttr->middlewareClasses);
        }
        
        return $middleware;
    }

    /**
     * Extract middleware classes from middleware group attributes
     * 
     * @param array $middlewareGroupAttributes
     * @return array<string>
     */
    private function getMiddlewareFromGroupAttributes(array $middlewareGroupAttributes): array
    {
        $middleware = [];
        
        foreach ($middlewareGroupAttributes as $attribute) {
            /** @var MiddlewareGroup $middlewareGroupAttr */
            $middlewareGroupAttr = $attribute->newInstance();
            $groupName = $middlewareGroupAttr->group;
            
            if (isset($this->middlewareGroups[$groupName])) {
                $middleware = array_merge($middleware, $this->middlewareGroups[$groupName]);
            }
        }
        
        return $middleware;
    }
}
