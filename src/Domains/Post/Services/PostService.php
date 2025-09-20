<?php

declare(strict_types=1);

namespace App\Domains\Post\Services;

use App\Domains\Post\Repositories\PostRepository;

class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {
    }

    /**
     * Get all posts
     */
    public function getAllPosts(): array
    {
        return $this->postRepository->findAll();
    }

    /**
     * Get posts with user information
     */
    public function getPostsWithUsers(): array
    {
        return $this->postRepository->getPostsWithUsers();
    }

    /**
     * Get post by ID
     */
    public function getPostById(int $id): ?array
    {
        return $this->postRepository->findById($id);
    }

    /**
     * Create a new post
     */
    public function createPost(array $postData): array
    {
        $userId = $postData['user_id'] ?? null;
        $title = $postData['title'] ?? '';
        $content = $postData['content'] ?? '';

        if (!$userId || empty($title)) {
            throw new \InvalidArgumentException('User ID and title are required');
        }

        $postId = $this->postRepository->createPost($userId, $title, $content);
        
        $post = $this->postRepository->findById($postId);
        if (!$post) {
            throw new \RuntimeException('Failed to create post');
        }

        return $post;
    }

    /**
     * Update post
     */
    public function updatePost(int $id, array $postData): array
    {
        // Check if post exists
        $existingPost = $this->postRepository->findById($id);
        if (!$existingPost) {
            throw new \InvalidArgumentException('Post not found');
        }

        // Prepare update data
        $updateData = [];
        if (isset($postData['title']) && !empty($postData['title'])) {
            $updateData['title'] = $postData['title'];
        }
        if (isset($postData['content'])) {
            $updateData['content'] = $postData['content'];
        }

        if (empty($updateData)) {
            throw new \InvalidArgumentException('No valid data to update');
        }

        $success = $this->postRepository->updatePost($id, $updateData);
        if (!$success) {
            throw new \RuntimeException('Failed to update post');
        }

        $post = $this->postRepository->findById($id);
        if (!$post) {
            throw new \RuntimeException('Failed to retrieve updated post');
        }

        return $post;
    }

    /**
     * Delete post
     */
    public function deletePost(int $id): bool
    {
        // Check if post exists
        $existingPost = $this->postRepository->findById($id);
        if (!$existingPost) {
            throw new \InvalidArgumentException('Post not found');
        }

        return $this->postRepository->delete($id);
    }

    /**
     * Search posts by title
     */
    public function searchPostsByTitle(string $title): array
    {
        if (empty($title)) {
            throw new \InvalidArgumentException('Title parameter is required');
        }

        return $this->postRepository->searchByTitle($title);
    }

    /**
     * Get posts by user ID
     */
    public function getPostsByUserId(int $userId): array
    {
        return $this->postRepository->findByUserId($userId);
    }

    /**
     * Get recent posts
     */
    public function getRecentPosts(int $limit = 10): array
    {
        return $this->postRepository->getRecentPosts($limit);
    }

    /**
     * Count posts by user
     */
    public function countPostsByUserId(int $userId): int
    {
        return $this->postRepository->countByUserId($userId);
    }
} 