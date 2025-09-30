<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Services;

use App\Domains\Database\Services\DatabaseService;

class RateLimitStorage
{
    private DatabaseService $databaseService;

    public function __construct(?DatabaseService $databaseService = null)
    {
        $this->databaseService = $databaseService ?? new DatabaseService();
    }

    /**
     * Get current request count for a given key
     */
    public function getCurrentCount(string $key): int
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        
        $stmt = $connection->prepare("
            SELECT request_count 
            FROM rate_limits 
            WHERE rate_key = ? AND expires_at > ?
        ");
        
        $stmt->execute([$key, $now]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? (int)$result : 0;
    }

    /**
     * Increment the request count for a given key
     */
    public function incrementCount(string $key, int $windowSeconds): int
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        $expiresAt = $now + $windowSeconds;
        
        // Try to update existing entry first
        $stmt = $connection->prepare("
            UPDATE rate_limits 
            SET request_count = request_count + 1, 
                updated_at = datetime('now')
            WHERE rate_key = ? AND expires_at > ?
        ");
        
        $stmt->execute([$key, $now]);
        
        // If no rows were affected, insert a new entry
        if ($stmt->rowCount() === 0) {
            $stmt = $connection->prepare("
                INSERT OR REPLACE INTO rate_limits (rate_key, request_count, expires_at, created_at, updated_at)
                VALUES (?, 1, ?, datetime('now'), datetime('now'))
            ");
            
            $stmt->execute([$key, $expiresAt]);
        }
        
        // Get the current count
        $stmt = $connection->prepare("
            SELECT request_count 
            FROM rate_limits 
            WHERE rate_key = ?
        ");
        
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? (int)$result : 1;
    }

    /**
     * Get the expiration time for a given key
     */
    public function getExpirationTime(string $key): ?int
    {
        $connection = $this->databaseService->getConnection();
        
        $stmt = $connection->prepare("
            SELECT expires_at 
            FROM rate_limits 
            WHERE rate_key = ?
        ");
        
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? (int)$result : null;
    }

    /**
     * Clean up expired entries
     */
    public function cleanup(): void
    {
        $connection = $this->databaseService->getConnection();
        $now = time();
        
        $stmt = $connection->prepare("
            DELETE FROM rate_limits 
            WHERE expires_at <= ?
        ");
        
        $stmt->execute([$now]);
    }

}