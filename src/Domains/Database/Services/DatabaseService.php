<?php

declare(strict_types=1);

namespace App\Domains\Database\Services;

use PDO;
use PDOException;

class DatabaseService
{
    private static ?PDO $connection = null;
    private string $databasePath;

    public function __construct(?string $databasePath = null)
    {
        $this->databasePath = $databasePath ?? $this->getDefaultDatabasePath();
    }

    /**
     * Get the PDO connection instance (singleton pattern)
     */
    public function getConnection(): PDO
    {
        if (self::$connection === null) {
            $this->connect();
        }

        return self::$connection;
    }

    /**
     * Create the database connection
     */
    private function connect(): void
    {
        try {
            // Ensure the database directory exists
            $this->ensureDatabaseDirectoryExists();

            $dsn = "sqlite:{$this->databasePath}";
            
            self::$connection = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Enable foreign key constraints
            self::$connection->exec('PRAGMA foreign_keys = ON');
            
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the default database path
     */
    private function getDefaultDatabasePath(): string
    {
        // Check for environment variable first
        $envPath = $_ENV['DATABASE_PATH'] ?? null;
        if ($envPath) {
            return $envPath;
        }

        // Fallback to project root data directory
        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'database.sqlite';
    }

    /**
     * Ensure the database directory exists
     */
    private function ensureDatabaseDirectoryExists(): void
    {
        $directory = dirname($this->databasePath);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Initialize database tables
     */
    public function initializeTables(): void
    {
        $connection = $this->getConnection();

        // Create users table
        $connection->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create posts table
        $connection->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Create rate_limits table
        $connection->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                rate_key VARCHAR(255) NOT NULL UNIQUE,
                request_count INTEGER NOT NULL DEFAULT 0,
                expires_at INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create cache_responses table
        $connection->exec("
            CREATE TABLE IF NOT EXISTS cache_responses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cache_key VARCHAR(255) NOT NULL UNIQUE,
                response_data TEXT NOT NULL,
                expires_at INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert sample data if tables are empty
        $this->insertSampleData();
    }

    /**
     * Insert sample users and posts if tables are empty
     */
    private function insertSampleData(): void
    {
        $connection = $this->getConnection();

        // Check if users table is empty
        $userCount = $connection->query("SELECT COUNT(*) FROM users")->fetchColumn();
        
        if ($userCount == 0) {
            // Insert sample users
            $users = [
                ['John Doe', 'john@example.com'],
                ['Jane Smith', 'jane@example.com'],
                ['Bob Johnson', 'bob@example.com'],
                ['Alice Brown', 'alice@example.com'],
                ['Charlie Wilson', 'charlie@example.com']
            ];

            $userStmt = $connection->prepare("
                INSERT INTO users (name, email, created_at, updated_at) 
                VALUES (?, ?, datetime('now'), datetime('now'))
            ");

            foreach ($users as $user) {
                $userStmt->execute($user);
            }
        }

        // Check if posts table is empty
        $postCount = $connection->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        
        if ($postCount == 0) {
            // Insert sample posts
            $posts = [
                [1, 'Welcome to Our Platform', 'This is the first post on our platform. Welcome everyone!'],
                [1, 'Getting Started Guide', 'Here\'s how to get started with our amazing features.'],
                [2, 'My First Experience', 'I just joined and I\'m loving it already!'],
                [2, 'Tips and Tricks', 'Here are some useful tips I\'ve discovered.'],
                [3, 'Community Guidelines', 'Let\'s keep our community friendly and respectful.'],
                [3, 'Feature Request', 'I have some ideas for new features we could add.'],
                [4, 'Success Story', 'How this platform helped me achieve my goals.'],
                [4, 'Weekly Update', 'Here\'s what I\'ve been working on this week.'],
                [5, 'Technical Discussion', 'Let\'s talk about the latest technology trends.'],
                [5, 'Project Showcase', 'Check out this amazing project I\'ve been working on!']
            ];

            $postStmt = $connection->prepare("
                INSERT INTO posts (user_id, title, content, created_at, updated_at) 
                VALUES (?, ?, ?, datetime('now'), datetime('now'))
            ");

            foreach ($posts as $post) {
                $postStmt->execute($post);
            }
        }
    }

    /**
     * Close the database connection
     */
    public function close(): void
    {
        self::$connection = null;
    }
} 