<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Enums;

class RateLimitInterval
{
    public const SECONDS = 1;
    public const MINUTES = 60;
    public const HOURS = 3600;
    public const DAYS = 86400;
}