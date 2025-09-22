<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Console;

use App\Domains\Infrastructure\Services\RouteDiscovery;
use DI\Container;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'route:list',
    description: 'Display all registered routes'
)]
class RouteListCommand extends Command
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get router from container (same as in index.php)
        $router = $this->container->get(Router::class);

        // Set up strategy to use DI container
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($this->container);
        $router->setStrategy($strategy);

        // Automatically discover controllers and register routes from attributes
        $routeDiscovery = $this->container->get(RouteDiscovery::class);
        $routeDiscovery->discoverRoutes(__DIR__ . '/../../../Domains');

        $routes = $this->extractRoutesFromRouter($router);

        if (empty($routes)) {
            $output->writeln('<info>No routes found.</info>');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Method', 'Path', 'Handler'])
            ->setRows($routes);

        $table->render();

        $output->writeln('');
        $output->writeln(sprintf('<info>Total routes: %d</info>', count($routes)));

        return Command::SUCCESS;
    }

    private function extractRoutesFromRouter(Router $router): array
    {
        $routes = [];
        
        try {
            // Use reflection to access the internal routes data structure
            $reflection = new \ReflectionClass($router);
            $routesProperty = $reflection->getProperty('routes');
            $routesProperty->setAccessible(true);
            $routeCollection = $routesProperty->getValue($router);

            // Check if it's an array or has iterator capabilities
                            if (is_array($routeCollection)) {
                    foreach ($routeCollection as $route) {
                        if (is_object($route) && method_exists($route, 'getMethod') && method_exists($route, 'getPath')) {
                            $method = $route->getMethod();
                            $methodStr = is_array($method) ? strtoupper(implode('|', $method)) : strtoupper($method);
                            
                            $routes[] = [
                                'Method' => $this->colorizeMethod($methodStr),
                                'Path' => $route->getPath(),
                                'Handler' => $this->getHandlerNameFromRoute($route)
                            ];
                        }
                    }
                } elseif (is_object($routeCollection)) {
                    // Try to iterate if it's an object with iterator
                    if ($routeCollection instanceof \Traversable) {
                        foreach ($routeCollection as $route) {
                            if (is_object($route) && method_exists($route, 'getMethod') && method_exists($route, 'getPath')) {
                                $method = $route->getMethod();
                                $methodStr = is_array($method) ? strtoupper(implode('|', $method)) : strtoupper($method);
                                
                                $routes[] = [
                                    'Method' => $this->colorizeMethod($methodStr),
                                    'Path' => $route->getPath(),
                                    'Handler' => $this->getHandlerNameFromRoute($route)
                                ];
                            }
                        }
                    }
                }
        } catch (\Exception $e) {
            // If reflection fails, we'll return an empty array
        }

        return $routes;
    }

    private function getHandlerNameFromRoute($route): string
    {
        try {
            // Use reflection to get the handler information without instantiating
            $reflection = new \ReflectionClass($route);
            $handlerProperty = $reflection->getProperty('handler');
            $handlerProperty->setAccessible(true);
            $handler = $handlerProperty->getValue($route);

            if (is_string($handler)) {
                // Handler is a string like "App\Controller::method"
                if (strpos($handler, '::') !== false) {
                    [$class, $method] = explode('::', $handler, 2);
                    $shortClass = basename(str_replace('\\', '/', $class));
                    return $shortClass . '::' . $method;
                }
                return $handler;
            }

            if (is_array($handler) && count($handler) === 2) {
                $class = is_object($handler[0]) ? get_class($handler[0]) : $handler[0];
                $method = $handler[1];
                
                // Simplify class name by removing namespace
                $shortClass = basename(str_replace('\\', '/', $class));
                
                return $shortClass . '::' . $method;
            }

            return 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getHandlerName($callable): string
    {
        if (is_array($callable) && count($callable) === 2) {
            $class = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
            $method = $callable[1];
            
            // Simplify class name by removing namespace
            $shortClass = basename(str_replace('\\', '/', $class));
            
            return $shortClass . '::' . $method;
        }

        if (is_string($callable)) {
            return $callable;
        }

        return 'Closure';
    }

    private function colorizeMethod(string $method): string
    {
        // Postman color scheme for HTTP methods
        return match($method) {
            'GET' => "\033[32m{$method}\033[0m",        // Green
            'POST' => "\033[33m{$method}\033[0m",       // Yellow/Orange
            'PUT' => "\033[34m{$method}\033[0m",        // Blue
            'PATCH' => "\033[36m{$method}\033[0m",      // Cyan
            'DELETE' => "\033[31m{$method}\033[0m",     // Red
            'HEAD' => "\033[37m{$method}\033[0m",       // White
            'OPTIONS' => "\033[35m{$method}\033[0m",    // Magenta
            default => $method                           // No color for unknown methods
        };
    }
} 