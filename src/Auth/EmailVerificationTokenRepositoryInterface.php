<?php

declare(strict_types=1);

namespace App\Auth;

interface EmailVerificationTokenRepositoryInterface
{
    public function findByHash(string $tokenHash): ?EmailVerificationToken;

    public function save(EmailVerificationToken $token): void;

    public function findAllExpiredOrUsed(): iterable;
}
