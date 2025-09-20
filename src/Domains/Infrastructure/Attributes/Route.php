<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly ?string $prefix = null,
        public readonly ?string $name = null
    ) {
    }
}
