<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Middleware;

use App\Domains\Infrastructure\Services\CacheStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class CacheMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheStorage $cacheStorage,
        private int $ttl
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Generate a unique cache key for this request
        $cacheKey = $this->generateCacheKey($request);
        
        // Try to get cached response
        $cachedResponse = $this->cacheStorage->get($cacheKey);
        
        if ($cachedResponse !== null) {
            // Return cached response
            $responseData = json_decode($cachedResponse, true);
            return new JsonResponse($responseData);
        }
        
        // Process the request
        $response = $handler->handle($request);
        
        // Only cache successful responses (2xx status codes)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            // Get response body
            $responseBody = (string) $response->getBody();
            
            // Store in cache
            $this->cacheStorage->set($cacheKey, $responseBody, $this->ttl);
            
            // Add cache headers to indicate this was cached
            $response = $response->withHeader('X-Cache-Status', 'MISS');
        }
        
        return $response;
    }

    /**
     * Generate a unique cache key based on request method, path, and query parameters
     */
    private function generateCacheKey(ServerRequestInterface $request): string
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $queryString = $request->getUri()->getQuery();
        
        // Include query parameters in cache key for GET requests
        $keyData = $method . ':' . $path;
        if ($queryString) {
            $keyData .= '?' . $queryString;
        }
        
        return 'cache:' . md5($keyData);
    }
}