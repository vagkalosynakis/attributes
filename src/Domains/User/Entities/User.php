<?php

declare(strict_types=1);

namespace App\Domains\User\Entities;

class User
{
    public string $name;
    public string $email;

    public function __construct(string $name = '', string $email = '')
    {
        $this->name = $name;
        $this->email = $email;
    }
} 