<?php

declare(strict_types=1);

namespace App\Domains\User\Services;

use App\Domains\User\Repositories\UserRepository;
use App\Domains\User\Models\User;

class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?array
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Create a new user
     */
    public function createUser(array $userData): array
    {
        $name = $userData['name'] ?? '';
        $email = $userData['email'] ?? '';

        if (empty($name) || empty($email)) {
            throw new \InvalidArgumentException('Name and email are required');
        }

        // Check if email already exists
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new \InvalidArgumentException('Email already exists');
        }

        $userId = $this->userRepository->createUser($name, $email);
        
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \RuntimeException('Failed to create user');
        }

        return $user;
    }

    /**
     * Update user
     */
    public function updateUser(int $id, array $userData): array
    {
        // Check if user exists
        $existingUser = $this->userRepository->findById($id);
        if (!$existingUser) {
            throw new \InvalidArgumentException('User not found');
        }

        // Prepare update data
        $updateData = [];
        if (isset($userData['name']) && !empty($userData['name'])) {
            $updateData['name'] = $userData['name'];
        }
        if (isset($userData['email']) && !empty($userData['email'])) {
            // Check if email already exists for another user
            $emailUser = $this->userRepository->findByEmail($userData['email']);
            if ($emailUser && $emailUser['id'] !== $id) {
                throw new \InvalidArgumentException('Email already exists');
            }
            $updateData['email'] = $userData['email'];
        }

        if (empty($updateData)) {
            throw new \InvalidArgumentException('No valid data to update');
        }

        $success = $this->userRepository->updateUser($id, $updateData);
        if (!$success) {
            throw new \RuntimeException('Failed to update user');
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \RuntimeException('Failed to retrieve updated user');
        }

        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser(int $id): bool
    {
        // Check if user exists
        $existingUser = $this->userRepository->findById($id);
        if (!$existingUser) {
            throw new \InvalidArgumentException('User not found');
        }

        return $this->userRepository->delete($id);
    }

    /**
     * Search users by name
     */
    public function searchUsersByName(string $name): array
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Name parameter is required');
        }

        return $this->userRepository->searchByName($name);
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Get users with post count
     */
    public function getUsersWithPostCount(): array
    {
        return $this->userRepository->getUsersWithPostCount();
    }
} 