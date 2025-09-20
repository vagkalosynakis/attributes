<?php

declare(strict_types=1);

namespace App\Domains\Test\Controllers;

use App\Domains\Infrastructure\Attributes\Middleware;
use App\Domains\Infrastructure\Attributes\Route;
use App\Domains\Infrastructure\Attributes\WithoutMiddleware;
use App\Domains\Infrastructure\Enums\MiddlewareGroups;
use App\Domains\Infrastructure\Middleware\LoggingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class TestController
{
    #[Route(method: 'GET', path: '/test1')]
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

    #[Route(method: 'GET', path: '/test2')]
    #[Middleware(MiddlewareGroups::GROUP_1_MIDDLEWARES)]
    public function test2(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to the Test page!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }

    #[Route(method: 'GET', path: '/test3')]
    #[Middleware([...MiddlewareGroups::GROUP_1_MIDDLEWARES, ...MiddlewareGroups::GROUP_2_MIDDLEWARES])]
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
