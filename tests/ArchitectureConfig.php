<?php

declare(strict_types=1);

/**
 * Architecture Test Configuration
 * 
 * Modify these values to adapt the architecture tests to different projects:
 * - Change DOMAIN_NAMESPACE to match your domain namespace pattern
 * - Change DOMAINS_PATH to match your domains directory structure  
 * - Change PUBLIC_ATTRIBUTE_CLASS to your public class attribute
 * - Change PUBLIC_DOMAINS to specify which domains should be always accessible
 */

// The root namespace for your domain classes (with trailing backslashes)
const DOMAIN_NAMESPACE = 'App\\Domains\\';

// The relative path from src/ to your domains directory (with leading slash)
const DOMAINS_PATH = '/Domains';

// The fully qualified class name of your public attribute class
const PUBLIC_ATTRIBUTE_CLASS = \App\Domains\Infrastructure\Attributes\PublicClass::class;

// Domains that are always public and can use/be used by anyone
const PUBLIC_DOMAINS = ['Infrastructure', 'Database'];

/**
 * Examples for other projects:
 * 
 * // For a project using different namespace:
 * const DOMAIN_NAMESPACE = 'MyApp\\Core\\';
 * const DOMAINS_PATH = '/Core';
 * const PUBLIC_ATTRIBUTE_CLASS = \MyApp\Core\Shared\Attributes\Public::class;
 * const PUBLIC_DOMAINS = ['Shared', 'Common'];
 * 
 * // For a project with different structure:
 * const DOMAIN_NAMESPACE = 'Company\\Modules\\';
 * const DOMAINS_PATH = '/Modules';
 * const PUBLIC_ATTRIBUTE_CLASS = \Company\Modules\Foundation\Attributes\PublicApi::class;
 * const PUBLIC_DOMAINS = ['Foundation', 'Infrastructure'];
 */ 