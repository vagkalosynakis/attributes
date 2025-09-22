<?php

declare(strict_types=1);

namespace App\Domains\Post\Entities;

class Post
{
    public string $title;
    public string $content;
    public int $user_id;

    public function __construct(string $title = '', string $content = '', int $user_id = 0)
    {
        $this->title = $title;
        $this->content = $content;
        $this->user_id = $user_id;
    }
} 