<?php

declare(strict_types=1);

namespace App\Domains\Post\Controllers;

use App\Domains\Infrastructure\Attributes\Middleware;
use App\Domains\Infrastructure\Attributes\Route;
use App\Domains\Infrastructure\Middleware\LoggingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

#[Middleware([LoggingMiddleware::class])]
class PostController
{
    private array $posts = [
        1 => ['id' => 1, 'title' => 'First Post', 'content' => 'This is the first post content'],
        2 => ['id' => 2, 'title' => 'Second Post', 'content' => 'This is the second post content'],
        3 => ['id' => 3, 'title' => 'Third Post', 'content' => 'This is the third post content']
    ];

    #[Route(method: 'GET', path: '/posts', prefix: 'api')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Posts endpoint',
            'posts' => array_values($this->posts),
            'count' => count($this->posts)
        ]);
    }

    #[Route(method: 'GET', path: '/posts/{id:number}', prefix: 'api')]
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        
        if (!isset($this->posts[$id])) {
            return new JsonResponse([
                'error' => 'Post not found',
                'id' => $id
            ], 404);
        }

        return new JsonResponse([
            'message' => 'Post details',
            'post' => $this->posts[$id]
        ]);
    }

    #[Route(method: 'POST', path: '/posts', prefix: 'api')]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $body = json_decode($request->getBody()->getContents(), true);
        
        $newId = max(array_keys($this->posts)) + 1;
        $newPost = [
            'id' => $newId,
            'title' => $body['title'] ?? 'Untitled',
            'content' => $body['content'] ?? '',
            'created_by' => $user['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->posts[$newId] = $newPost;
        
        return new JsonResponse([
            'message' => 'Post created successfully',
            'post' => $newPost,
            'user' => $user
        ], 201);
    }
}
