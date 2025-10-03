<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Services;

use App\Domains\Database\Services\DatabaseService;

class CacheStorage
{
    private DatabaseService $databaseService;

    public function __construct(?DatabaseService $databaseService = null)
    {
        $this->databaseService = $databaseService ?? new DatabaseService();
    }

    /**
     * Get cached response for a given key
     */
    public function get(string $key): ?string
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        
        $stmt = $connection->prepare("
            SELECT response_data 
            FROM cache_responses 
            WHERE cache_key = ? AND expires_at > ?
        ");
        
        $stmt->execute([$key, $now]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? (string)$result : null;
    }

    /**
     * Store response in cache
     */
    public function set(string $key, string $responseData, int $ttl): void
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        $expiresAt = $now + $ttl;
        
        $stmt = $connection->prepare("
            INSERT OR REPLACE INTO cache_responses (cache_key, response_data, expires_at, created_at, updated_at)
            VALUES (?, ?, ?, datetime('now'), datetime('now'))
        ");
        
        $stmt->execute([$key, $responseData, $expiresAt]);
    }

    /**
     * Check if cache key exists and is not expired
     */
    public function has(string $key): bool
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        
        $stmt = $connection->prepare("
            SELECT 1 
            FROM cache_responses 
            WHERE cache_key = ? AND expires_at > ?
        ");
        
        $stmt->execute([$key, $now]);
        $result = $stmt->fetchColumn();
        
        return $result !== false;
    }

    /**
     * Delete cached response
     */
    public function delete(string $key): void
    {
        $connection = $this->databaseService->getConnection();
        
        $stmt = $connection->prepare("
            DELETE FROM cache_responses 
            WHERE cache_key = ?
        ");
        
        $stmt->execute([$key]);
    }

    /**
     * Clean up expired cache entries
     */
    public function cleanup(): void
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        
        $stmt = $connection->prepare("
            DELETE FROM cache_responses 
            WHERE expires_at <= ?
        ");
        
        $stmt->execute([$now]);
    }
}