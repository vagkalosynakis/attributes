<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class WithoutMiddleware
{
    /**
     * @param array<string> $middlewareClasses Array of middleware class names to exclude
     */
    public function __construct(
        public array $middlewareClasses
    ) {
    }
} 