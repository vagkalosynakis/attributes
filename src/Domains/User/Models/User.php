<?php

declare(strict_types=1);

namespace App\Domains\User\Models;

class User
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly string $email = '',
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null
    ) {
    }

    /**
     * Create a User instance from database array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }

    /**
     * Convert User instance to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
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
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    /**
     * Get data for database update (excluding id and created_at)
     */
    public function getUpdateData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    }
} 