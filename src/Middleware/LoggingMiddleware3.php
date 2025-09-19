<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggingMiddleware3 implements MiddlewareInterface
{
    private string $logFile;

    public function __construct(?string $logFile = null)
    {
        // Use absolute path based on project root with proper directory separator
        $this->logFile = $logFile ?? dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'requests3.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        
        // Process the request
        $response = $handler->handle($request);
        
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        
        // Log the request
        $logEntry = $this->formatLogEntry($request, $response, $duration);
        $this->writeLog($logEntry);
        
        // Add a header to confirm middleware is running
        return $response;
    }

    private function formatLogEntry(ServerRequestInterface $request, ResponseInterface $response, float $duration): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $statusCode = $response->getStatusCode();
        $userAgent = $request->getHeaderLine('User-Agent');
        $ip = $this->getClientIp($request);
        
        return sprintf(
            "[%s] %s %s - %d - %s ms - IP: %s - User-Agent: %s\n",
            $timestamp,
            $method,
            $uri,
            $statusCode,
            $duration,
            $ip,
            $userAgent
        );
    }

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

    private function writeLog(string $logEntry): void
    {
        // Simple file write - if it fails, it fails silently
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
} 