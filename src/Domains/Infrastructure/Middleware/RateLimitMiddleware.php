<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Middleware;

use App\Domains\Infrastructure\Services\RateLimitStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RateLimitStorage $storage,
        private int $amount,
        private int $intervalSeconds
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Generate a unique key for this client and route
        $key = $this->generateKey($request);
        
        // Get current count
        $currentCount = $this->storage->getCurrentCount($key);
        
        // Check if limit is exceeded
        if ($currentCount >= $this->amount) {
            $expirationTime = $this->storage->getExpirationTime($key);
            $resetTime = $expirationTime ? date('Y-m-d H:i:s', $expirationTime) : 'unknown';
            
            return new JsonResponse([
                'error' => 'Rate limit exceeded',
                'message' => "Too many requests. Limit: {$this->amount} requests per {$this->intervalSeconds} seconds",
                'retry_after' => $expirationTime ? $expirationTime - time() : 0,
                'reset_time' => $resetTime
            ], 429, [
                'X-RateLimit-Limit' => (string) $this->amount,
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Reset' => (string) ($expirationTime ?? time()),
                'Retry-After' => (string) ($expirationTime ? $expirationTime - time() : 0)
            ]);
        }
        
        // Increment the count
        $newCount = $this->storage->incrementCount($key, $this->intervalSeconds);
        
        // Process the request
        $response = $handler->handle($request);
        
        // Add rate limit headers to successful responses
        $remaining = max(0, $this->amount - $newCount);
        $expirationTime = $this->storage->getExpirationTime($key);
        
        return $response->withHeader('X-RateLimit-Limit', (string) $this->amount)
                       ->withHeader('X-RateLimit-Remaining', (string) $remaining)
                       ->withHeader('X-RateLimit-Reset', (string) ($expirationTime ?? time()));
    }

    /**
     * Generate a unique key for rate limiting based on client IP and route
     */
    private function generateKey(ServerRequestInterface $request): string
    {
        $clientIp = $this->getClientIp($request);
        $route = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        return 'rate_limit:' . md5($clientIp . ':' . $method . ':' . $route);
    }

    /**
     * Get the client IP address
     */
    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        // Check for IP from various headers (for reverse proxies)
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($serverParams[$header])) {
                $ip = $serverParams[$header];
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return 'unknown';
    }
}