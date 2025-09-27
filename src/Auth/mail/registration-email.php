<?php

declare(strict_types=1);


$verificationUrl = $urlGenerator->generate(
    'auth/verify-email',
    ['token' => $rawToken],
);

echo sprintf('Please verify your email by clicking this link: %s', $verificationUrl);
