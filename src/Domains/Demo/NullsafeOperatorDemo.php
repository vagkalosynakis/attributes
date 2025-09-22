<?php

declare(strict_types=1);

namespace App\Domains\Demo;

/**
 * PHP 7.4 Style - Verbose Null Checking
 * 
 * TODO: Refactor to PHP 8 Nullsafe Operator
 * - Replace verbose null checks with ?-> operator
 * - Eliminate nested if statements
 * - Use short-circuit evaluation for cleaner code
 */
class NullsafeOperatorDemo
{
    public function getUserCity(?object $user): ?string
    {
        // PHP 7.4 - Verbose null checking required
        if ($user !== null && 
            isset($user->profile) && 
            $user->profile !== null &&
            isset($user->profile->address) && 
            $user->profile->address !== null &&
            isset($user->profile->address->city)) {
            return $user->profile->address->city;
        }
        
        return null;
    }

    public function getCompanyName(?object $user): ?string
    {
        // Another example of verbose null checking
        if ($user !== null) {
            if (isset($user->employment) && $user->employment !== null) {
                if (isset($user->employment->company) && $user->employment->company !== null) {
                    if (isset($user->employment->company->name)) {
                        return $user->employment->company->name;
                    }
                }
            }
        }
        
        return null;
    }

    public function getAccountBalance(?object $user): ?float
    {
        // Yet another verbose null check example
        $balance = null;
        
        if ($user !== null && 
            property_exists($user, 'account') &&
            $user->account !== null &&
            property_exists($user->account, 'wallet') &&
            $user->account->wallet !== null &&
            property_exists($user->account->wallet, 'balance')) {
            $balance = $user->account->wallet->balance;
        }
        
        return $balance;
    }

    private function createSampleUsers(): array
    {
        // Create sample data for demonstration
        $userWithFullData = (object)[
            'name' => 'John Doe',
            'profile' => (object)[
                'address' => (object)[
                    'street' => '123 Main St',
                    'city' => 'New York',
                    'country' => 'USA'
                ]
            ],
            'employment' => (object)[
                'company' => (object)[
                    'name' => 'Tech Corp',
                    'industry' => 'Technology'
                ]
            ],
            'account' => (object)[
                'wallet' => (object)[
                    'balance' => 1500.75
                ]
            ]
        ];

        $userWithPartialData = (object)[
            'name' => 'Jane Smith',
            'profile' => (object)[
                'address' => null
            ],
            'employment' => null,
            'account' => (object)[
                'wallet' => null
            ]
        ];

        return [$userWithFullData, $userWithPartialData];
    }

    public function demonstrate(): array
    {
        [$fullUser, $partialUser] = $this->createSampleUsers();

        return [
            'current_style' => 'PHP 7.4 - Verbose null checking with nested conditions',
            'issues' => [
                'Very verbose and hard to read',
                'Prone to errors and typos',
                'Difficult to maintain',
                'Performance overhead from multiple checks',
                'Easy to forget edge cases'
            ],
            'examples' => [
                'full_user_city' => $this->getUserCity($fullUser),
                'partial_user_city' => $this->getUserCity($partialUser),
                'null_user_city' => $this->getUserCity(null),
                'full_user_company' => $this->getCompanyName($fullUser),
                'partial_user_company' => $this->getCompanyName($partialUser),
                'full_user_balance' => $this->getAccountBalance($fullUser),
                'partial_user_balance' => $this->getAccountBalance($partialUser)
            ],
            'refactoring_goal' => 'Use nullsafe operator (?->) for concise null-safe property access'
        ];
    }
} 