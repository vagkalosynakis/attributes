<?php

declare(strict_types=1);

namespace App\Domains\Post\Models;

class Post
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?int $user_id = null,
        public readonly string $title = '',
        public readonly string $content = '',
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null
    ) {
    }

    /**
     * Create a Post instance from database array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: $data['user_id'] ?? null,
            title: $data['title'] ?? '',
            content: $data['content'] ?? '',
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }

    /**
     * Convert Post instance to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get data for database insertion (excluding id and timestamps)
     */
    public function getInsertData(): array
    {
        return [
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    /**
     * Get data for database update (excluding id, user_id and created_at)
     */
    public function getUpdateData(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
} 