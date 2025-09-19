<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class MiddlewareGroup
{
    public function __construct(
        public readonly string $group
    ) {
    }
} 