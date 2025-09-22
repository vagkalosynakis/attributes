<?php

declare(strict_types=1);

namespace App\Domains\Demo\Controllers;

use App\Domains\Demo\PropertyPromotionDemo;
use App\Domains\Demo\UnionTypesDemo;
use App\Domains\Demo\MatchExpressionDemo;
use App\Domains\Demo\NullsafeOperatorDemo;
use App\Domains\Infrastructure\Attributes\Route;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DemoController
{
    #[Route(method: 'GET', path: '/demo')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $propertyPromotionDemo = new PropertyPromotionDemo();
        $unionTypesDemo = new UnionTypesDemo();
        $matchExpressionDemo = new MatchExpressionDemo();
        $nullsafeOperatorDemo = new NullsafeOperatorDemo();

        $propertyResult = $propertyPromotionDemo->demonstrate();
        $unionResult = $unionTypesDemo->demonstrate();
        $matchResult = $matchExpressionDemo->demonstrate();
        $nullsafeResult = $nullsafeOperatorDemo->demonstrate();

        $html = $this->generateHtml([
            'property_promotion' => $propertyResult,
            'union_types' => $unionResult,
            'match_expression' => $matchResult,
            'nullsafe_operator' => $nullsafeResult
        ]);

        return new HtmlResponse($html, 200);
    }

    private function generateHtml(array $results): string
    {
        return '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>🚀 PHP 8 Features Demo</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    padding: 20px;
                }
                
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    overflow: hidden;
                }
                
                .header {
                    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                    color: white;
                    padding: 40px;
                    text-align: center;
                }
                
                .header h1 {
                    font-size: 2.5rem;
                    margin-bottom: 10px;
                    font-weight: 300;
                }
                
                .header p {
                    font-size: 1.2rem;
                    opacity: 0.9;
                }
                
                .content {
                    padding: 40px;
                }
                
                .feature {
                    margin-bottom: 50px;
                    border: 1px solid #e1e5e9;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                }
                
                .feature-header {
                    background: #f8f9fa;
                    padding: 20px;
                    border-bottom: 1px solid #e1e5e9;
                }
                
                .feature-title {
                    font-size: 1.5rem;
                    color: #2c3e50;
                    margin-bottom: 5px;
                }
                
                .feature-description {
                    color: #6c757d;
                    font-size: 1rem;
                }
                
                .feature-content {
                    padding: 30px;
                }
                
                .demo-section {
                    margin-bottom: 30px;
                }
                
                .demo-section h4 {
                    color: #495057;
                    margin-bottom: 15px;
                    font-size: 1.1rem;
                }
                
                .results-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }
                
                .result-card {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 10px;
                    padding: 20px;
                }
                
                .result-card h5 {
                    color: #495057;
                    margin-bottom: 10px;
                    font-size: 1rem;
                }
                
                .result-value {
                    background: #e9ecef;
                    padding: 10px;
                    border-radius: 5px;
                    font-family: "Courier New", monospace;
                    font-size: 0.9rem;
                    color: #495057;
                    word-break: break-all;
                }
                
                .issues-list, .benefits-list {
                    list-style: none;
                    margin-top: 15px;
                }
                
                .issues-list li, .benefits-list li {
                    padding: 8px 0;
                    border-bottom: 1px solid #e9ecef;
                    position: relative;
                    padding-left: 25px;
                }
                
                .issues-list li:before {
                    content: "⚠️";
                    position: absolute;
                    left: 0;
                }
                
                .benefits-list li:before {
                    content: "✅";
                    position: absolute;
                    left: 0;
                }
                
                .current-style {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    margin-bottom: 20px;
                }
                
                .refactoring-goal {
                    background: #d1ecf1;
                    border: 1px solid #bee5eb;
                    border-radius: 8px;
                    padding: 15px;
                    margin-top: 20px;
                    font-weight: 500;
                }
                
                .footer {
                    background: #2c3e50;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    font-size: 0.9rem;
                }
                
                @media (max-width: 768px) {
                    .header h1 {
                        font-size: 2rem;
                    }
                    
                    .content {
                        padding: 20px;
                    }
                    
                    .results-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚀 PHP 8 Features Demo</h1>
                    <p>Live Refactoring Examples - From PHP 7.4 to PHP 8</p>
                </div>
                
                <div class="content">
                    ' . $this->generatePropertyPromotionSection($results['property_promotion']) . '
                    ' . $this->generateUnionTypesSection($results['union_types']) . '
                    ' . $this->generateMatchExpressionSection($results['match_expression']) . '
                    ' . $this->generateNullsafeOperatorSection($results['nullsafe_operator']) . '
                </div>
                
                <div class="footer">
                    <p>🔧 Ready for live refactoring demonstration! Each section shows working PHP 7.4 code that can be modernized to PHP 8.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    private function generatePropertyPromotionSection(array $data): string
    {
        return '<div class="feature">
            <div class="feature-header">
                <h3 class="feature-title">📝 Constructor Property Promotion</h3>
                <p class="feature-description">Refactor verbose constructor assignments</p>
            </div>
            <div class="feature-content">
                <div class="demo-section">
                    <h4>🎯 Working Example Results:</h4>
                    <div class="results-grid">
                        <div class="result-card">
                            <h5>User Name</h5>
                            <div class="result-value">' . htmlspecialchars($data['user_data']['name']) . '</div>
                        </div>
                        <div class="result-card">
                            <h5>Email</h5>
                            <div class="result-value">' . htmlspecialchars($data['user_data']['email']) . '</div>
                        </div>
                        <div class="result-card">
                            <h5>Age</h5>
                            <div class="result-value">' . htmlspecialchars((string)$data['user_data']['age']) . '</div>
                        </div>
                        <div class="result-card">
                            <h5>Active Status</h5>
                            <div class="result-value">' . ($data['user_data']['is_active'] ? 'true' : 'false') . '</div>
                        </div>
                    </div>
                </div>
                
                <div class="refactoring-goal">
                    <strong>🎯 Refactoring Goal:</strong> ' . htmlspecialchars($data['refactoring_goal']) . '
                </div>
            </div>
        </div>';
    }

    private function generateUnionTypesSection(array $data): string
    {
        $examplesHtml = '';
        foreach ($data['examples'] as $key => $value) {
            $examplesHtml .= '<div class="result-card">
                <h5>' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</h5>
                <div class="result-value">' . htmlspecialchars((string)$value) . '</div>
            </div>';
        }

        return '<div class="feature">
            <div class="feature-header">
                <h3 class="feature-title">🔗 Union Types</h3>
                <p class="feature-description">Replace docblock types with native union types</p>
            </div>
            <div class="feature-content">
                <div class="demo-section">
                    <h4>🎯 Working Examples:</h4>
                    <div class="results-grid">' . $examplesHtml . '</div>
                </div>
                
                <div class="refactoring-goal">
                    <strong>🎯 Refactoring Goal:</strong> ' . htmlspecialchars($data['refactoring_goal']) . '
                </div>
            </div>
        </div>';
    }

    private function generateMatchExpressionSection(array $data): string
    {
        $examplesHtml = '';
        foreach ($data['examples'] as $key => $value) {
            $examplesHtml .= '<div class="result-card">
                <h5>' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</h5>
                <div class="result-value">' . htmlspecialchars((string)$value) . '</div>
            </div>';
        }

        return '<div class="feature">
            <div class="feature-header">
                <h3 class="feature-title">🎯 Match Expression</h3>
                <p class="feature-description">Convert switch statements to match expressions</p>
            </div>
            <div class="feature-content">
                <div class="demo-section">
                    <h4>🎯 Working Examples:</h4>
                    <div class="results-grid">' . $examplesHtml . '</div>
                </div>
                
                <div class="refactoring-goal">
                    <strong>🎯 Refactoring Goal:</strong> ' . htmlspecialchars($data['refactoring_goal']) . '
                </div>
            </div>
        </div>';
    }

    private function generateNullsafeOperatorSection(array $data): string
    {
        $examplesHtml = '';
        foreach ($data['examples'] as $key => $example) {
            $value = $example === null ? 'null' : (string)$example;
            $examplesHtml .= '<div class="result-card">
                <h5>' . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . '</h5>
                <div class="result-value">' . htmlspecialchars($value) . '</div>
            </div>';
        }

        return '<div class="feature">
            <div class="feature-header">
                <h3 class="feature-title">🛡️ Nullsafe Operator</h3>
                <p class="feature-description">Replace verbose null checks with nullsafe operator</p>
            </div>
            <div class="feature-content">
                <div class="demo-section">
                    <h4>🎯 Working Examples:</h4>
                    <div class="results-grid">' . $examplesHtml . '</div>
                </div>
                
                <div class="refactoring-goal">
                    <strong>🎯 Refactoring Goal:</strong> ' . htmlspecialchars($data['refactoring_goal']) . '
                </div>
            </div>
        </div>';
    }
} 