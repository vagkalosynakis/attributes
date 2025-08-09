<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class TestController
{
    #[Route('GET', '/test', 'test')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to the Test page!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }
}
