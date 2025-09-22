<?php

declare(strict_types=1);

namespace App\Domains\Demo;

/**
 * PHP 7.4 Style - Using Switch Statements
 * 
 * TODO: Refactor to PHP 8 Match Expressions
 * - Replace switch statements with match expressions
 * - Remove break statements (no fall-through in match)
 * - Use match as expression that returns values
 * - Benefit from strict comparison (===) by default
 */
class MatchExpressionDemo
{
    public function getHttpStatusMessage(int $statusCode): string
    {
        switch ($statusCode) {
            case 200:
                return 'OK';
            case 201:
                return 'Created';
            case 400:
                return 'Bad Request';
            case 401:
                return 'Unauthorized';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Not Found';
            case 422:
                return 'Unprocessable Entity';
            case 500:
                return 'Internal Server Error';
            default:
                return 'Unknown Status Code';
        }
    }

    public function calculateDiscount(string $customerType): float
    {
        switch ($customerType) {
            case 'premium':
                $discount = 0.20;
                break;
            case 'gold':
                $discount = 0.15;
                break;
            case 'silver':
                $discount = 0.10;
                break;
            case 'bronze':
                $discount = 0.05;
                break;
            default:
                $discount = 0.0;
                break;
        }
        
        return $discount;
    }

    public function getFileIcon(string $extension): string
    {
        // Switch with multiple cases
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return '🖼️';
            case 'pdf':
                return '📄';
            case 'doc':
            case 'docx':
                return '📝';
            case 'zip':
            case 'rar':
                return '📦';
            default:
                return '📄';
        }
    }

    public function demonstrate(): array
    {
        return [
            'current_style' => 'PHP 7.4 - Switch statements with break statements',
            'issues' => [
                'Requires break statements to prevent fall-through',
                'Fall-through behavior can cause bugs',
                'Switch is a statement, not an expression',
                'Loose comparison (==) by default',
                'More verbose syntax'
            ],
            'examples' => [
                'http_status_200' => $this->getHttpStatusMessage(200),
                'http_status_404' => $this->getHttpStatusMessage(404),
                'http_status_999' => $this->getHttpStatusMessage(999),
                'premium_discount' => $this->calculateDiscount('premium'),
                'regular_discount' => $this->calculateDiscount('regular'),
                'pdf_icon' => $this->getFileIcon('pdf'),
                'jpg_icon' => $this->getFileIcon('jpg'),
                'unknown_icon' => $this->getFileIcon('xyz')
            ],
            'refactoring_goal' => 'Convert to match expressions for cleaner, safer code'
        ];
    }
} 