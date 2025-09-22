<?php

declare(strict_types=1);

use App\Domains\Infrastructure\Container\ContainerConfig;
use App\Domains\Infrastructure\Services\RouteDiscovery;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseInterface;

class TestCase
{
    protected Router $router;
    protected $container;

    public function setUp(): void
    {
        // Create DI Container
        $this->container = ContainerConfig::create();

        // Get router from container
        $this->router = $this->container->get(Router::class);

        // Set up strategy to use DI container
        $strategy = new ApplicationStrategy();
        $strategy->setContainer($this->container);
        $this->router->setStrategy($strategy);

        // Automatically discover controllers and register routes from attributes
        $routeDiscovery = $this->container->get(RouteDiscovery::class);
        $routeDiscovery->discoverRoutes(__DIR__ . '/../src/Domains');
    }

    public function makeRequest(string $method, string $uri, array $data = []): ResponseInterface
    {
        $request = ServerRequestFactory::fromGlobals()
            ->withMethod($method)
            ->withUri(new \Laminas\Diactoros\Uri($uri));

        if (!empty($data)) {
            $body = new \Laminas\Diactoros\Stream('php://memory', 'w');
            $body->write(json_encode($data));
            $body->rewind();
            
            $request = $request->withHeader('Content-Type', 'application/json')
                ->withBody($body);
        }

        return $this->router->dispatch($request);
    }

    public function get(string $uri): ResponseInterface
    {
        return $this->makeRequest('GET', $uri);
    }

    public function post(string $uri, array $data = []): ResponseInterface
    {
        return $this->makeRequest('POST', $uri, $data);
    }

    public function put(string $uri, array $data = []): ResponseInterface
    {
        return $this->makeRequest('PUT', $uri, $data);
    }

    public function delete(string $uri): ResponseInterface
    {
        return $this->makeRequest('DELETE', $uri);
    }

    public function getResponseData(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        return json_decode($body, true) ?? [];
    }
} 