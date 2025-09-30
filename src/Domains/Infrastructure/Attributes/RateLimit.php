<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Attributes;

use App\Domains\Infrastructure\Enums\RateLimitInterval;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RateLimit
{
    public function __construct(
        public int $amount,
        public int $intervalSeconds
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Rate limit amount must be a positive integer');
        }
        if ($intervalSeconds <= 0) {
            throw new \InvalidArgumentException('Rate limit interval must be a positive integer');
        }
    }

}