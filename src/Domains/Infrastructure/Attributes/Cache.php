<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cache
{
    public function __construct(
        public int $ttl
    ) {
        if ($ttl <= 0) {
            throw new \InvalidArgumentException('Cache TTL must be a positive integer');
        }
    }
}