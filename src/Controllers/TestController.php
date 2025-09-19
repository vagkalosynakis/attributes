<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Attributes\Middleware;
use App\Attributes\MiddlewareGroup;
use App\Attributes\Route;
use App\Middleware\LoggingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class TestController
{
    #[Route('GET', '/test1')]
    #[Middleware([LoggingMiddleware::class])]
    public function test1(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to the Test page!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }

    #[Route('GET', '/test2')]
    #[MiddlewareGroup('group_1')]
    public function test2(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to the Test page!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }
}
