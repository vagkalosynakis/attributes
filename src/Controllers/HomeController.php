<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class HomeController
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Welcome to the Home page!',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri()
        ]);
    }

    public function about(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'About page',
            'description' => 'This is a simple PHP 8.4 application with League Router and PHP-DI',
            'version' => '1.0.0'
        ]);
    }
}
