<?php

declare(strict_types=1);

namespace App\Domains\User\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
    }
} 