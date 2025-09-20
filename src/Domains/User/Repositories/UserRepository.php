<?php

declare(strict_types=1);

namespace App\Domains\User\Repositories;

use App\Domains\Database\Repositories\BaseRepository;
use App\Domains\Database\Services\DatabaseService;

class UserRepository extends BaseRepository
{
    protected string $table = 'users';

    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct($databaseService);
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new user
     */
    public function createUser(string $name, string $email): int
    {
        return $this->create([
            'name' => $name,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update user information
     */
    public function updateUser(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Get users with their post count
     */
    public function getUsersWithPostCount(): array
    {
        $sql = "
            SELECT 
                u.*, 
                COUNT(p.id) as post_count 
            FROM {$this->table} u 
            LEFT JOIN posts p ON u.id = p.user_id 
            GROUP BY u.id
        ";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Search users by name
     */
    public function searchByName(string $name): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE name LIKE :name");
        $stmt->bindValue(':name', "%{$name}%");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
} 