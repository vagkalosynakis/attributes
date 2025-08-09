<?php

declare(strict_types=1);

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly ?string $name = null
    ) {
    }
}
