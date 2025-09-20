<?php

declare(strict_types=1);

namespace App\Domains\User\Controllers;

use App\Domains\Infrastructure\Attributes\Route;
use App\Domains\User\Services\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class UserController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    #[Route(method: 'GET', path: '/users', prefix: 'api')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $users = $this->userService->getAllUsers();
            
            return new JsonResponse([
                'success' => true,
                'data' => $users,
                'count' => count($users)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route(method: 'GET', path: '/users/{id:number}', prefix: 'api')]
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = (int) $request->getAttribute('id');
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route(method: 'POST', path: '/users', prefix: 'api')]
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $body = json_decode((string) $request->getBody(), true);
            
            if (!$body) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data'
                ], 400);
            }
            
            $user = $this->userService->createUser($body);
            
            return new JsonResponse([
                'success' => true,
                'data' => $user,
                'message' => 'User created successfully'
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route(method: 'PUT', path: '/users/{id:number}', prefix: 'api')]
    public function update(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = (int) $request->getAttribute('id');
            $body = json_decode((string) $request->getBody(), true);
            
            if (!$body) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON data'
                ], 400);
            }
            
            $user = $this->userService->updateUser($id, $body);
            
            return new JsonResponse([
                'success' => true,
                'data' => $user,
                'message' => 'User updated successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route(method: 'DELETE', path: '/users/{id:number}', prefix: 'api')]
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = (int) $request->getAttribute('id');
            
            $this->userService->deleteUser($id);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 