<?php

declare(strict_types=1);

namespace App\Domains\Post\Repositories;

use App\Domains\Database\Repositories\BaseRepository;
use App\Domains\Database\Services\DatabaseService;

class PostRepository extends BaseRepository
{
    protected string $table = 'posts';

    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct($databaseService);
    }

    /**
     * Create a new post
     */
    public function createPost(int $userId, string $title, string $content): int
    {
        return $this->create([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Update post information
     */
    public function updatePost(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Find posts by user ID
     */
    public function findByUserId(int $userId): array
    {
        return $this->findWhere(['user_id' => $userId]);
    }

    /**
     * Search posts by title
     */
    public function searchByTitle(string $title): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE title LIKE :title");
        $stmt->bindValue(':title', "%{$title}%");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get posts with user information
     */
    public function getPostsWithUsers(): array
    {
        $sql = "
            SELECT 
                p.*, 
                u.name as user_name,
                u.email as user_email
            FROM {$this->table} p 
            LEFT JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get recent posts (limit)
     */
    public function getRecentPosts(int $limit = 10): array
    {
        $sql = "
            SELECT 
                p.*, 
                u.name as user_name
            FROM {$this->table} p 
            LEFT JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT :limit
        ";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Count posts by user
     */
    public function countByUserId(int $userId): int
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
} 