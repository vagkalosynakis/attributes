<?php

declare(strict_types=1);

namespace Tests;

use App\Domains\Infrastructure\Attributes\PublicClass;

/**
 * Architecture Test Configuration
 * 
 * Modify these values to adapt the architecture tests to different projects:
 * - Change DOMAIN_NAMESPACE to match your domain namespace pattern
 * - Change DOMAINS_PATH to match your domains directory structure  
 * - Change PUBLIC_ATTRIBUTE_CLASS to your public class attribute
 * - Change PUBLIC_DOMAINS to specify which domains should be always accessible
 */
class ArchitectureConfig
{
    // The root namespace for your domain classes (with trailing backslashes)
    public const DOMAIN_NAMESPACE = 'App\\Domains\\';

    // The relative path from src/ to your domains directory (with leading slash)
    public const DOMAINS_PATH = '/Domains';

    // The fully qualified class name of your public attribute class
    public const PUBLIC_ATTRIBUTE_CLASS = PublicClass::class;

    // Domains that are always public and can use/be used by anyone
    public const PUBLIC_DOMAINS = ['Infrastructure', 'Database'];
}