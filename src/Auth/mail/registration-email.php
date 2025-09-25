<?php

declare(strict_types=1);

use App\User\User;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var User $user
 * @var string $rawToken
 * @var UrlGeneratorInterface $urlGenerator
 */

$verificationUrl = $urlGenerator->generate(
    'auth/verify-email',
    ['token' => $rawToken],
);

echo sprintf('Please verify your email by clicking this link: %s', $verificationUrl);
