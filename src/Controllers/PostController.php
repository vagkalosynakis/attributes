<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class PostController
{
    private array $posts = [
        1 => ['id' => 1, 'title' => 'First Post', 'content' => 'This is the first post content'],
        2 => ['id' => 2, 'title' => 'Second Post', 'content' => 'This is the second post content'],
        3 => ['id' => 3, 'title' => 'Third Post', 'content' => 'This is the third post content']
    ];

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'message' => 'Posts endpoint',
            'posts' => array_values($this->posts),
            'count' => count($this->posts)
        ]);
    }

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
}
