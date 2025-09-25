<?php

declare(strict_types=1);

namespace App\Auth;

use App\User\User;

final readonly class UserRegistered
{
    public function __construct(
        public User $user,
        public string $rawToken
    ) {
    }
}
