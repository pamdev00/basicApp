<?php

declare(strict_types=1);

namespace App\VO;

use Stringable;

final readonly class PasswordHash implements Stringable
{
    private function __construct(private string $value)
    {
    }

    public static function fromPlain(string $password): self
    {
        return new self(password_hash($password, PASSWORD_ARGON2ID));
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function verify(string $password): bool
    {
        return password_verify($password, $this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
