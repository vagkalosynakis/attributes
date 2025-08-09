<?php

declare(strict_types=1);

namespace App\Services;

use App\Attributes\Route;
use DirectoryIterator;
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
