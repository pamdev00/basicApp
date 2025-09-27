<?php

declare(strict_types=1);

namespace App\VO;

use InvalidArgumentException;
use Stringable;

final readonly class Email implements Stringable
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $email): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        return new self($email);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
