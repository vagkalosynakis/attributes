<?php

declare(strict_types=1);

namespace App\Domains\Demo;

/**
 * PHP 7.4 Style - Using Docblock Types with Manual Type Checking
 * 
 * TODO: Refactor to PHP 8 Union Types
 * - Replace @param docblock with native union types: string|int|float
 * - Remove manual type checking with is_* functions
 * - Let PHP handle type enforcement natively
 */
class UnionTypesDemo
{
    public function processValue($value): string
    {
        // Manual type checking required in PHP 7.4
        if (is_string($value)) {
            return 'String value: ' . strtoupper($value);
        }
        
        if (is_int($value)) {
            return 'Integer value: ' . ($value * 2);
        }
        
        if (is_float($value)) {
            return 'Float value: ' . round($value, 2);
        }
        
        throw new \InvalidArgumentException('Unsupported type: ' . gettype($value));
    }

    public function calculateArea(int|float $width, int|float $height): float
    {
        // Manual validation needed
        if (!is_numeric($width) || !is_numeric($height)) {
            throw new \InvalidArgumentException('Width and height must be numeric');
        }
        
        return (float)$width * (float)$height;
    }

    public function demonstrate(): array
    {
        return [
            'current_style' => 'PHP 7.4 - Docblock types with manual validation',
            'issues' => [
                'No runtime type enforcement',
                'Manual type checking with is_* functions',
                'Docblocks can become outdated',
                'No IDE autocomplete for union types'
            ],
            'examples' => [
                'string_processing' => $this->processValue('hello world'),
                'integer_processing' => $this->processValue(42),
                'float_processing' => $this->processValue(3.14159),
                'area_calculation' => $this->calculateArea(10, 5.5)
            ],
            'refactoring_goal' => 'Use native union types for automatic type enforcement'
        ];
    }
} 