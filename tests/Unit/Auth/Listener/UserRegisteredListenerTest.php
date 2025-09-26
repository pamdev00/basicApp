<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Listener;

use App\Auth\Listener\UserRegisteredListener;
use App\Auth\RegistrationMailer;
use App\Auth\UserRegistered;
use App\User\User;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\Message;
use Yiisoft\Router\UrlGeneratorInterface;

final class UserRegisteredListenerTest extends TestCase
{
    public function testInvokeSendsEmail(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('test@example.com');

        // Mocks for UserRegisteredListener
        $listenerLogger = $this->createMock(LoggerInterface::class);
        $verificationRouteName = $this->getVerificationRouteName();

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generateAbsolute')
            ->with($verificationRouteName, ['token' => 'test-token'])
            ->willReturn('http://test.com/verify?token=test-token');

        // Mocks for RegistrationMailer
        $mailerLogger = $this->createMock(LoggerInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(Message::class));

        // Create a real RegistrationMailer with mocked dependencies
        $registrationMailer = new RegistrationMailer(
            $mailerLogger,
            $mailer,
            'from@example.com',
            'to@example.com'
        );

        // Create the listener with the real mailer service
        $listener = new UserRegisteredListener(
            $listenerLogger,
            $registrationMailer,
            $urlGenerator,
            $verificationRouteName
        );
        $event = new UserRegistered($user, 'test-token');

        ($listener)($event);
    }

    private function getVerificationRouteName(): string
    {
        $this->ensureDatabaseEnv();

        $params = require dirname(__DIR__, 4) . '/config/common/params.php';

        return $params['app']['auth']['verificationRouteName'];
    }

    private function ensureDatabaseEnv(): void
    {
        $defaults = [
            'DB_NAME' => 'app',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '5432',
            'DB_USERNAME' => 'app',
            'DB_PASSWORD' => 'secret',
        ];

        foreach ($defaults as $name => $value) {
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
        }
    }
}
