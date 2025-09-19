<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Attributes\Middleware;
use App\Attributes\MiddlewareGroup;
use App\Attributes\Route;
use App\Attributes\WithoutMiddleware;
use App\Middleware\LoggingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class TestController
{
    #[Route('test', 'GET', '/test1')]
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

    #[Route('test', 'GET', '/test2')]
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

    #[Route('test', 'GET', '/test3')]
    #[MiddlewareGroup('group_1')]
    #[WithoutMiddleware([LoggingMiddleware::class])]
    public function test3(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to the Test page!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }
}
