<?php

declare(strict_types=1);

namespace App\Auth\Listener;

use App\Auth\RegistrationMailer;
use App\Auth\UserRegistered;
use Psr\Log\LoggerInterface;
use Throwable;
use Yiisoft\Router\UrlGeneratorInterface;

final readonly class UserRegisteredListener
{
    public function __construct(
        private LoggerInterface $logger,
        private RegistrationMailer $registrationMailer,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(UserRegistered $event): void
    {
        $user = $event->user;
        // TODO: The route for email verification does not exist yet. We need to create it.
        // For now, let's assume its name will be 'auth/verify-email'.
        $verificationUrl = $this->urlGenerator->generateAbsolute(
            name: 'auth/verify-email',
          arguments: ['token' => $event->rawToken],
        );

        $this->logger->info('Generated verification URL: {url}', ['url' => $verificationUrl]);

        $subject = 'Email verification';
        $htmlBody = sprintf(
            'Please verify your email by clicking this link: <a href="%1$s">%1$s</a>',
            $verificationUrl
        );

        $this->registrationMailer->send($user->getEmail(), $subject, $htmlBody);
    }
}
