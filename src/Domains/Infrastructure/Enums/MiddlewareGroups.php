<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Enums;

use App\Domains\Infrastructure\Middleware\LoggingMiddleware;
use App\Domains\Infrastructure\Middleware\LoggingMiddleware2;
use App\Domains\Infrastructure\Middleware\LoggingMiddleware3;
use App\Domains\Infrastructure\Middleware\LoggingMiddleware4;

class MiddlewareGroups
{
    public const GROUP_1 = 'group_1';
    public const GROUP_2 = 'group_2';

    // Constant arrays for use in attributes (PHP 8.0 compatible)
    public const GROUP_1_MIDDLEWARES = [
        LoggingMiddleware::class,
        LoggingMiddleware2::class,
    ];

    public const GROUP_2_MIDDLEWARES = [
        LoggingMiddleware3::class,
        LoggingMiddleware4::class,
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function GROUP_1(): self
    {
        return new self(self::GROUP_1);
    }

    public static function GROUP_2(): self
    {
        return new self(self::GROUP_2);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the middleware classes for this group
     * 
     * @return array<string>
     */
    public function getMiddlewares(): array
    {
        return match($this->value) {
            self::GROUP_1 => self::GROUP_1_MIDDLEWARES,
            self::GROUP_2 => self::GROUP_2_MIDDLEWARES,
            default => [],
        };
    }

    /**
     * Get all middleware classes from multiple groups
     * 
     * @param MiddlewareGroups ...$groups
     * @return array<string>
     */
    public static function getMiddlewaresFromGroups(MiddlewareGroups ...$groups): array
    {
        $middlewares = [];
        
        foreach ($groups as $group) {
            $middlewares = array_merge($middlewares, $group->getMiddlewares());
        }
        
        return array_unique($middlewares);
    }

    /**
     * Get middleware by group name (for runtime usage)
     * 
     * @param string $groupName
     * @return array<string>
     */
    public static function getMiddlewaresByName(string $groupName): array
    {
        return match($groupName) {
            self::GROUP_1 => self::GROUP_1_MIDDLEWARES,
            self::GROUP_2 => self::GROUP_2_MIDDLEWARES,
            default => [],
        };
    }
} 