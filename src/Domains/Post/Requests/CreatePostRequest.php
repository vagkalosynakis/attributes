<?php

declare(strict_types=1);

namespace App\Domains\Post\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class CreatePostRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public string $content;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public int $user_id;

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->user_id = (int)($data['user_id'] ?? 0);
    }
} 