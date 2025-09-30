<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Services;

use App\Domains\Infrastructure\Attributes\Middleware;
use App\Domains\Infrastructure\Attributes\RateLimit;
use App\Domains\Infrastructure\Attributes\Route;
use App\Domains\Infrastructure\Attributes\WithoutMiddleware;
use App\Domains\Infrastructure\Middleware\RateLimitMiddleware;

use DirectoryIterator;
use League\Route\Router;
use ReflectionClass;
use ReflectionMethod;
use DI\Container;

class RouteDiscovery
{


    public function __construct(
        private Router $router,
        private Container $container,
        private RateLimitStorage $rateLimitStorage
    ) {
    }

    /**
     * Discover controllers in all domain directories and register their routes
     * 
     * @param string $domainsPath Path to the Domains directory (e.g., '/path/to/src/Domains')
     */
    public function discoverRoutes(string $domainsPath): void
    {
        $controllerDirectories = $this->findAllControllerDirectories($domainsPath);
        
        foreach ($controllerDirectories as $directory) {
            $controllers = $this->discoverControllersInDirectory($directory);
            
            foreach ($controllers as $controllerClass) {
                $this->registerRoutesForController($controllerClass);
            }
        }
    }

    /**
     * Find all Controllers directories within domain directories
     * 
     * @param string $domainsPath Path to the Domains directory
     * @return array<string> Array of controller directory paths
     */
    private function findAllControllerDirectories(string $domainsPath): array
    {
        $controllerDirectories = [];
        
        if (!is_dir($domainsPath)) {
            return $controllerDirectories;
        }

        $iterator = new DirectoryIterator($domainsPath);
        
        foreach ($iterator as $domainDir) {
            if ($domainDir->isDot() || !$domainDir->isDir()) {
                continue;
            }
            
            // Skip the Infrastructure domain for controller scanning
            if ($domainDir->getFilename() === 'Infrastructure') {
                continue;
            }
            
            // Check if this domain has a Controllers directory
            $controllersPath = $domainDir->getPathname() . DIRECTORY_SEPARATOR . 'Controllers';
            if (is_dir($controllersPath)) {
                $controllerDirectories[] = $controllersPath;
            }
        }
        
        return $controllerDirectories;
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
        // Normalize the directory path to use forward slashes
        $normalizedDir = str_replace('\\', '/', $directory);
        
        // Extract domain from path structure: src/Domains/{Domain}/Controllers
        if (preg_match('/Domains\/([^\/]+)\/Controllers/', $normalizedDir, $matches)) {
            $domainName = $matches[1];
            return "App\\Domains\\{$domainName}\\Controllers\\{$className}";
        }
        
        // Fallback for other structures
        return 'App\\' . $className;
    }

    private function registerRoutesForController(string $controllerClass): void
    {
        $reflectionClass = new ReflectionClass($controllerClass);
        
        // Get class-level middleware
        $classMiddleware = $this->getMiddlewareFromAttributes($reflectionClass->getAttributes(Middleware::class));
        
        // Get class-level middleware exclusions
        $classExcludedMiddleware = $this->getExcludedMiddlewareFromAttributes($reflectionClass->getAttributes(WithoutMiddleware::class));
        
        // Remove excluded middleware
        $classMiddleware = array_diff($classMiddleware, $classExcludedMiddleware);
        
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttributes = $method->getAttributes(Route::class);
            
            foreach ($routeAttributes as $routeAttribute) {
                /** @var Route $route */
                $route = $routeAttribute->newInstance();
                
                // Build the final route path with prefix (if provided)
                $finalPath = $this->buildRoutePath($route->prefix ?? '', $route->path);
                
                // Get method-level middleware
                $methodMiddleware = $this->getMiddlewareFromAttributes($method->getAttributes(Middleware::class));
                
                // Get method-level middleware exclusions
                $methodExcludedMiddleware = $this->getExcludedMiddlewareFromAttributes($method->getAttributes(WithoutMiddleware::class));
                
                // Remove excluded middleware
                $methodMiddleware = array_diff($methodMiddleware, $methodExcludedMiddleware);
                
                // Check for RateLimit attribute and add rate limit middleware
                $rateLimitMiddleware = $this->getRateLimitMiddleware($method);
                
                // Combine class and method middleware (class middleware first)
                $allMiddleware = array_merge($classMiddleware, $methodMiddleware);
                
                // Register the route
                $leagueRoute = $this->router->map(
                    strtoupper($route->method),
                    $finalPath,
                    [$controllerClass, $method->getName()]
                );
                
                // Add middleware to the route
                foreach ($allMiddleware as $middlewareClass) {
                    $leagueRoute->middleware($this->container->get($middlewareClass));
                }
                
                // Add rate limit middleware if present (after other middleware)
                if ($rateLimitMiddleware !== null) {
                    $leagueRoute->middleware($rateLimitMiddleware);
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
     * Extract middleware classes to exclude from WithoutMiddleware attributes
     * 
     * @param array $withoutMiddlewareAttributes
     * @return array<string>
     */
    private function getExcludedMiddlewareFromAttributes(array $withoutMiddlewareAttributes): array
    {
        $excludedMiddleware = [];
        
        foreach ($withoutMiddlewareAttributes as $attribute) {
            /** @var WithoutMiddleware $withoutMiddlewareAttr */
            $withoutMiddlewareAttr = $attribute->newInstance();
            $excludedMiddleware = array_merge($excludedMiddleware, $withoutMiddlewareAttr->middlewareClasses);
        }
        
        return $excludedMiddleware;
    }

    /**
     * Build the final route path by combining prefix and route path
     */
    private function buildRoutePath(string $prefix, string $routePath): string
    {
        $parts = [];
        
        // Add prefix if provided and not empty
        if ($prefix !== '' && $prefix !== null) {
            $parts[] = trim($prefix, '/');
        }
        
        // Add the route path
        $parts[] = trim($routePath, '/');
        
        // Join with slashes and ensure it starts with a slash
        $finalPath = '/' . implode('/', array_filter($parts));
        
        // Handle root path case
        return $finalPath === '/' ? '/' : rtrim($finalPath, '/');
    }

    /**
     * Get rate limit middleware instance if RateLimit attribute is present
     */
    private function getRateLimitMiddleware(ReflectionMethod $method): ?RateLimitMiddleware
    {
        $rateLimitAttributes = $method->getAttributes(RateLimit::class);
        
        if (empty($rateLimitAttributes)) {
            return null;
        }
        
        /** @var RateLimit $rateLimit */
        $rateLimit = $rateLimitAttributes[0]->newInstance();
        
        return new RateLimitMiddleware(
            $this->rateLimitStorage,
            $rateLimit->amount,
            $rateLimit->intervalSeconds
        );
    }
}
