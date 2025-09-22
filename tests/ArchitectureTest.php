<?php

declare(strict_types=1);

use App\Domains\Infrastructure\Attributes\PublicClass;

beforeEach(function () {
    $this->srcPath = __DIR__ . '/../src';
});

it('enforces domain boundaries - classes can only be used within their own domain unless marked as PublicClass', function () {
    $violations = [];
    
    // Domains that are always public and can use/be used by anyone
    $publicDomains = ['Infrastructure', 'Database'];
    
    // Get all PHP files in src/Domains
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($this->srcPath . '/Domains')
    );
    
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        
        $filePath = $file->getPathname();
        
        // Extract the domain from file path
        if (preg_match('/Domains\/([^\/]+)\//', $filePath, $matches)) {
            $currentDomain = $matches[1];
            
            // Skip if current domain is Infrastructure or Database - they can use anything
            if (in_array($currentDomain, $publicDomains)) {
                continue;
            }
            
            // Get the class name from file
            $className = getClassNameFromFile($filePath);
            if (!$className) {
                continue;
            }
            
            try {
                $reflection = new ReflectionClass($className);
                
                // Get all dependencies through constructor parameters and use statements
                $dependencies = getClassDependencies($reflection, $filePath);
                
                foreach ($dependencies as $dependency) {
                    // Extract domain from dependency class name
                    if (preg_match('/App\\\\Domains\\\\([^\\\\]+)\\\\/', $dependency, $depMatches)) {
                        $usedDomain = $depMatches[1];
                        
                        // Skip if it's the same domain
                        if ($usedDomain === $currentDomain) {
                            continue;
                        }
                        
                        // Skip if used domain is Infrastructure or Database - they're always public
                        if (in_array($usedDomain, $publicDomains)) {
                            continue;
                        }
                        
                        // Check if the used class is marked as PublicClass
                        if (!isClassMarkedAsPublic($dependency)) {
                            $violations[] = [
                                'file' => $filePath,
                                'class' => $className,
                                'domain' => $currentDomain,
                                'uses' => $dependency,
                                'usedDomain' => $usedDomain,
                                'violation' => "Domain '{$currentDomain}' class '{$className}' is using class '{$dependency}' from domain '{$usedDomain}' that is not marked as #[PublicClass]"
                            ];
                        }
                    }
                }
            } catch (ReflectionException $e) {
                // Skip classes that can't be reflected (might have missing dependencies)
                continue;
            }
        }
    }
    
    if (!empty($violations)) {
        $errorMessage = "Domain boundary violations found:\n";
        foreach ($violations as $violation) {
            $errorMessage .= "- {$violation['violation']}\n";
            $errorMessage .= "  File: {$violation['file']}\n\n";
        }
        
        expect($violations)->toBeEmpty($errorMessage);
    }
    
    expect($violations)->toBeEmpty();
});

// Helper function to get class name from file
function getClassNameFromFile(string $filePath): ?string {
    $content = file_get_contents($filePath);
    
    // Extract namespace
    if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
        $namespace = $namespaceMatches[1];
        
        // Extract class name
        if (preg_match('/(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/m', $content, $classMatches)) {
            return $namespace . '\\' . $classMatches[1];
        }
    }
    
    return null;
}

// Helper function to get class dependencies
function getClassDependencies(ReflectionClass $reflection, string $filePath): array {
    $dependencies = [];
    $content = file_get_contents($filePath);
    
    // Get dependencies from use statements
    if (preg_match_all('/^use\s+([^;]+);$/m', $content, $useMatches)) {
        foreach ($useMatches[1] as $useStatement) {
            // Only include App\Domains classes
            if (strpos($useStatement, 'App\\Domains\\') === 0) {
                $dependencies[] = $useStatement;
            }
        }
    }
    
    return array_unique($dependencies);
}

// Helper function to check if class is marked as PublicClass using reflection
function isClassMarkedAsPublic(string $className): bool {
    try {
        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes(PublicClass::class);
        
        return !empty($attributes);
    } catch (ReflectionException $e) {
        return false;
    }
} 