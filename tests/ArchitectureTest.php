<?php

declare(strict_types=1);

use Tests\ArchitectureConfig;

beforeEach(function () {
    $this->srcPath = __DIR__ . '/../src';
    $this->config = [
        'domainNamespace' => ArchitectureConfig::DOMAIN_NAMESPACE,
        'domainsPath' => $this->srcPath . ArchitectureConfig::DOMAINS_PATH,
        'publicAttributeClass' => ArchitectureConfig::PUBLIC_ATTRIBUTE_CLASS,
        'publicDomains' => ArchitectureConfig::PUBLIC_DOMAINS
    ];
});

it('enforces domain boundaries - classes can only be used within their own domain unless marked as PublicClass', function () {
    $violations = [];
    
    // Get all classes in the domains
    $classes = getAllDomainClasses($this->config['domainsPath']);
    
    foreach ($classes as $className) {
        try {
            $reflection = new ReflectionClass($className);
            $currentDomain = extractDomainFromClassName($className, $this->config['domainNamespace']);
            
            // Skip if current domain is in public domains - they can use anything
            if (in_array($currentDomain, $this->config['publicDomains'])) {
                continue;
            }
            
            // Get all class dependencies using reflection with location info
            $dependencies = getClassDependenciesViaReflection($reflection, $this->config['domainNamespace']);
            
            foreach ($dependencies as $dependencyInfo) {
                $dependency = $dependencyInfo['class'];
                $usedDomain = extractDomainFromClassName($dependency, $this->config['domainNamespace']);
                
                // Skip if not a domain class or same domain
                if (!$usedDomain || $usedDomain === $currentDomain) {
                    continue;
                }
                
                // Skip if used domain is in public domains - they're always public
                if (in_array($usedDomain, $this->config['publicDomains'])) {
                    continue;
                }
                
                // Check if the used class is marked as PublicClass
                if (!isClassMarkedAsPublic($dependency, $this->config['publicAttributeClass'])) {
                    $violations[] = [
                        'class' => $className,
                        'domain' => $currentDomain,
                        'uses' => $dependency,
                        'usedDomain' => $usedDomain,
                        'location' => $dependencyInfo['location'],
                        'violation' => "Domain '{$currentDomain}' class '{$className}' is using class '{$dependency}' from domain '{$usedDomain}' that is not marked as #[PublicClass]"
                    ];
                }
            }
        } catch (ReflectionException $e) {
            // Skip classes that can't be reflected (might have missing dependencies)
            continue;
        }
    }
    
    if (!empty($violations)) {
        $errorMessage = "\n🚨 DOMAIN BOUNDARY VIOLATIONS FOUND:\n\n";
        foreach ($violations as $violation) {
            $errorMessage .= "❌ {$violation['violation']}\n";
            $errorMessage .= "   📁 File: {$violation['location']['file']}\n";
            $errorMessage .= "   📍 Location: {$violation['location']['context']}\n";
            if (isset($violation['location']['line']) && $violation['location']['line']) {
                $errorMessage .= "   🔢 Line: {$violation['location']['line']}\n";
            }
            $errorMessage .= "\n";
        }
        
        // Output the detailed error message directly
        echo $errorMessage;
        
        // Fail the test with a simple message
        expect(count($violations))->toBe(0, "Found " . count($violations) . " domain boundary violation(s). See details above.");
    }
    
    // Always assert that we checked for violations (avoids "risky" test warning)
    expect(true)->toBeTrue();
});

// Get all classes in domain directories using reflection
function getAllDomainClasses(string $domainsPath): array {
    $classes = [];
    
    // Get all PHP files
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($domainsPath)
    );
    
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        
        $className = getClassNameFromFile($file->getPathname());
        if ($className && class_exists($className)) {
            $classes[] = $className;
        }
    }
    
    return $classes;
}

// Extract class name from file using reflection-friendly approach
function getClassNameFromFile(string $filePath): ?string {
    $content = file_get_contents($filePath);
    
    // Extract namespace
    if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
        $namespace = $namespaceMatches[1];
        
        // Extract class/interface/trait/enum name
        if (preg_match('/(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/m', $content, $classMatches)) {
            return $namespace . '\\' . $classMatches[1];
        }
    }
    
    return null;
}

// Extract domain from class name
function extractDomainFromClassName(string $className, string $domainNamespace): ?string {
    $escapedNamespace = preg_quote($domainNamespace, '/');
    if (preg_match('/' . $escapedNamespace . '([^\\\\]+)\\\\/', $className, $matches)) {
        return $matches[1];
    }
    return null;
}

// Get class dependencies using pure reflection with location information
function getClassDependenciesViaReflection(ReflectionClass $reflection, string $domainNamespace): array {
    $dependencies = [];
    $className = $reflection->getName();
    $fileName = $reflection->getFileName();
    
    // Get dependencies from constructor parameters
    $constructor = $reflection->getConstructor();
    if ($constructor) {
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if (str_starts_with($typeName, $domainNamespace)) {
                    $dependencies[] = [
                        'class' => $typeName,
                        'location' => [
                            'file' => $fileName,
                            'line' => $constructor->getStartLine(),
                            'context' => "Constructor parameter \${$parameter->getName()}"
                        ]
                    ];
                }
            }
        }
    }
    
    // Get dependencies from method parameters (excluding constructor)
    foreach ($reflection->getMethods() as $method) {
        if ($method->getDeclaringClass()->getName() !== $className || $method->isConstructor()) {
            continue; // Skip inherited methods and constructor (already handled above)
        }
        
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if (str_starts_with($typeName, $domainNamespace)) {
                    $dependencies[] = [
                        'class' => $typeName,
                        'location' => [
                            'file' => $fileName,
                            'line' => $method->getStartLine(),
                            'context' => "Method parameter \${$parameter->getName()} in {$method->getName()}()"
                        ]
                    ];
                }
            }
        }
    }
    
    // Get dependencies from property types (PHP 7.4+)
    foreach ($reflection->getProperties() as $property) {
        if ($property->getDeclaringClass()->getName() !== $className) {
            continue; // Skip inherited properties
        }
        
        $type = $property->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();
            if (str_starts_with($typeName, $domainNamespace)) {
                $dependencies[] = [
                    'class' => $typeName,
                                            'location' => [
                            'file' => $fileName,
                            'line' => null, // Properties don't have line numbers in reflection
                            'context' => "Property \${$property->getName()}"
                        ]
                ];
            }
        }
    }
    
    // Get dependencies from method return types
    foreach ($reflection->getMethods() as $method) {
        if ($method->getDeclaringClass()->getName() !== $className) {
            continue; // Skip inherited methods
        }
        
        $returnType = $method->getReturnType();
        if ($returnType instanceof ReflectionNamedType && !$returnType->isBuiltin()) {
            $typeName = $returnType->getName();
            if (str_starts_with($typeName, $domainNamespace)) {
                $dependencies[] = [
                    'class' => $typeName,
                                            'location' => [
                            'file' => $fileName,
                            'line' => $method->getStartLine(),
                            'context' => "Return type of {$method->getName()}()"
                        ]
                ];
            }
        }
    }
    
    // Get dependencies from parent class
    $parent = $reflection->getParentClass();
    if ($parent && str_starts_with($parent->getName(), $domainNamespace)) {
        $dependencies[] = [
            'class' => $parent->getName(),
            'location' => [
                'file' => $fileName,
                'line' => $reflection->getStartLine(),
                'context' => "Parent class of {$className}"
            ]
        ];
    }
    
    // Get dependencies from interfaces
    foreach ($reflection->getInterfaces() as $interface) {
        if (str_starts_with($interface->getName(), $domainNamespace)) {
            $dependencies[] = [
                'class' => $interface->getName(),
                'location' => [
                    'file' => $fileName,
                    'line' => $reflection->getStartLine(),
                    'context' => "Interface implemented by {$className}"
                ]
            ];
        }
    }
    
    // Get dependencies from traits
    foreach ($reflection->getTraits() as $trait) {
        if (str_starts_with($trait->getName(), $domainNamespace)) {
            $dependencies[] = [
                'class' => $trait->getName(),
                'location' => [
                    'file' => $fileName,
                    'line' => $reflection->getStartLine(),
                    'context' => "Trait used by {$className}"
                ]
            ];
        }
    }
    
    // Remove duplicates based on class name
    $unique = [];
    $seen = [];
    foreach ($dependencies as $dependency) {
        $key = $dependency['class'] . '|' . $dependency['location']['context'];
        if (!isset($seen[$key])) {
            $unique[] = $dependency;
            $seen[$key] = true;
        }
    }
    
    return $unique;
}

// Check if class is marked as PublicClass using reflection
function isClassMarkedAsPublic(string $className, string $publicAttributeClass): bool {
    try {
        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes($publicAttributeClass);
        
        return !empty($attributes);
    } catch (ReflectionException $e) {
        return false;
    }
} 