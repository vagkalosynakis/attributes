<?php

declare(strict_types=1);

namespace App\Domains\Post\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePostRequest
{
    #[Assert\Length(min: 3, max: 255)]
    public ?string $title = null;

    #[Assert\Length(min: 10)]
    public ?string $content = null;

    #[Assert\Type('integer')]
    #[Assert\Positive]
    public ?int $user_id = null;

    public function __construct(array $data)
    {
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->content = !empty($data['content']) ? $data['content'] : null;
        $this->user_id = !empty($data['user_id']) ? (int)$data['user_id'] : null;
    }
} 