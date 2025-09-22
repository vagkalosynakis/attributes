<?php

declare(strict_types=1);

namespace App\Domains\Demo;

/**
 * PHP 7.4 Style - Verbose Constructor Property Assignment
 * 
 * TODO: Refactor to PHP 8 Constructor Property Promotion
 * - Move visibility keywords to constructor parameters
 * - Remove property declarations
 * - Remove manual assignments in constructor body
 */
class PropertyPromotionDemo
{
    private string $name;
    private string $email;
    private int $age;
    private bool $isActive;

    public function __construct(
        string $name = 'Demo User',
        string $email = 'demo@example.com',
        int $age = 25,
        bool $isActive = true
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;
        $this->isActive = $isActive;
    }

    public static function createSampleUser(): self
    {
        return new self('John Doe', 'john@example.com', 30, true);
    }

    public function demonstrate(): array
    {
        // Create an instance to show it works (using static method to avoid constructor in controller)
        $user = self::createSampleUser();
        
        return [
            'current_style' => 'PHP 7.4 - Verbose constructor with manual property assignment',
            'lines_of_code' => 'Constructor: 8 lines, Properties: 4 lines = 12 total lines',
            'user_data' => [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'age' => $user->getAge(),
                'is_active' => $user->isActive()
            ],
            'refactoring_goal' => 'Reduce to 4 lines total using constructor property promotion'
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
} 