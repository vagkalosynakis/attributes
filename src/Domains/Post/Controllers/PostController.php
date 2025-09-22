<?php

declare(strict_types=1);

namespace App\Domains\Post\Controllers;

use App\Domains\Infrastructure\Attributes\Route;
use App\Domains\Post\Requests\CreatePostRequest;
use App\Domains\Post\Requests\UpdatePostRequest;
use App\Domains\Post\Services\PostService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController
{
    private ValidatorInterface $validator;

    public function __construct(
        private PostService $postService
    ) {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    }

    #[Route(method: 'GET', path: '/posts', prefix: 'api')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $posts = $this->postService->getPostsWithUsers();
            
            return new JsonResponse([
                'success' => true,
                'data' => $posts,
                'count' => count($posts)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route(method: 'GET', path: '/posts/{id:number}', prefix: 'api')]
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = (int) $request->getAttribute('id');
            $post = $this->postService->getPostById($id);
            
            if (!$post) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Post not found'
                ], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'data' => $post
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route(method: 'POST', path: '/posts', prefix: 'api')]
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
            $request = new CreatePostRequest($body);
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
            
            $postData = $this->postService->createPost($body);
            
            return new JsonResponse([
                'success' => true,
                'data' => $postData,
                'message' => 'Post created successfully'
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

    #[Route(method: 'PUT', path: '/posts/{id:number}', prefix: 'api')]
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
            $updateRequest = new UpdatePostRequest($body);
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
            
            $postData = $this->postService->updatePost($id, $body);
            
            return new JsonResponse([
                'success' => true,
                'data' => $postData,
                'message' => 'Post updated successfully'
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

    #[Route(method: 'DELETE', path: '/posts/{id:number}', prefix: 'api')]
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = (int) $request->getAttribute('id');
            
            $this->postService->deletePost($id);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Post deleted successfully'
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

    #[Route(method: 'GET', path: '/posts/search', prefix: 'api')]
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $queryParams = $request->getQueryParams();
            $title = $queryParams['title'] ?? '';
            
            $posts = $this->postService->searchPostsByTitle($title);
            
            return new JsonResponse([
                'success' => true,
                'data' => $posts,
                'count' => count($posts)
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
}
