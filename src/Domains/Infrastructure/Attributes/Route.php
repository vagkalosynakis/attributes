<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string $method,
        public string $path,
        public ?string $prefix = null,
        public ?string $name = null
    ) {
    }
}
