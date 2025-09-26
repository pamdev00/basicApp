<?php

declare(strict_types=1);

namespace App;

final class AuthorProvider
{
    public function __construct(private string $author)
    {
    }

    public function getAuthor(): string
    {
        return $this->author;
    }
}
