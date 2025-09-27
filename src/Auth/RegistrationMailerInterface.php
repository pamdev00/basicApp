<?php

declare(strict_types=1);

namespace App\Auth;

interface RegistrationMailerInterface
{
    public function send(string $to, string $subject, string $htmlBody): void;
}
