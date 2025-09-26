<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Listener;

use App\Auth\Listener\UserRegisteredListener;
use App\Auth\RegistrationMailerInterface;
use App\Auth\UserRegistered;
use App\User\User;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Throwable;
use Yiisoft\Router\UrlGeneratorInterface;

final class UserRegisteredListenerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testListenSuccess(): void
    {
        $user = new User('test-login', 'password', 'test@example.com', 'en-US', 'UTC');
        $rawToken = 'test-token';
        $event = new UserRegistered($user, $rawToken);

        $logger = $this->createMock(LoggerInterface::class);
        $mailer = $this->createMock(RegistrationMailerInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $verificationUrl = 'http://test.com/verify/test-token';
        $urlGenerator
            ->expects($this->once())
            ->method('generateAbsolute')
            ->with('auth/verify', ['token' => $rawToken])
            ->willReturn($verificationUrl);

        $mailer
            ->expects($this->once())
            ->method('send')
            ->with(
                $user->getEmail(),
                'Email verification',
                sprintf(
                    'Please verify your email by clicking this link: <a href="%1$s">%1$s</a>',
                    $verificationUrl
                )
            );

        $listener = new UserRegisteredListener(
            $logger,
            $mailer,
            $urlGenerator,
            'auth/verify'
        );

        $listener($event);
    }
}