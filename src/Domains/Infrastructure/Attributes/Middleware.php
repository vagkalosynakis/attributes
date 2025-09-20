<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Middleware
{
    /**
     * @param array<string> $middlewareClasses Array of middleware class names
     */
    public function __construct(
        readonly array $middlewareClasses
    ) { }
}
