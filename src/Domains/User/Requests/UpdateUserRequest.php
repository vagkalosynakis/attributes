<?php

declare(strict_types=1);

namespace App\Domains\User\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest
{
    #[Assert\Length(min: 2, max: 100)]
    public ?string $name = null;

    #[Assert\Email]
    public ?string $email = null;

    public function __construct(array $data)
    {
        $this->name = !empty($data['name']) ? $data['name'] : null;
        $this->email = !empty($data['email']) ? $data['email'] : null;
    }
} 