<?php

declare(strict_types=1);

namespace App\Domains\User\Controllers;

use App\Domains\Infrastructure\Attributes\Route;
use App\Domains\User\Requests\CreateUserRequest;
use App\Domains\User\Requests\UpdateUserRequest;
use App\Domains\User\Services\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController
{
    private ValidatorInterface $validator;

    public function __construct(
        private UserService $userService
    ) {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
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
            
            // Create and validate request object
            $request = new CreateUserRequest($body);
            $errors = $this->validator->validate($request);
            
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Validation failed',
                    'validation_errors' => $errorMessages
                ], 422);
            }
            
            $userData = $this->userService->createUser($body);
            
            return new JsonResponse([
                'success' => true,
                'data' => $userData,
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
            
            // Create and validate request object
            $updateRequest = new UpdateUserRequest($body);
            $errors = $this->validator->validate($updateRequest);
            
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Validation failed',
                    'validation_errors' => $errorMessages
                ], 422);
            }
            
            $userData = $this->userService->updateUser($id, $body);
            
            return new JsonResponse([
                'success' => true,
                'data' => $userData,
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