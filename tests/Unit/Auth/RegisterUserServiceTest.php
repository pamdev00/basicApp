<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth;

use App\Auth\EmailVerificationTokenRepositoryInterface;
use App\Auth\RegisterUserService;
use App\Auth\UserRegistered;
use App\User\User;
use App\User\UserRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Security\PasswordHasher;

final class RegisterUserServiceTest extends TestCase
{
    public function testRegisterSuccessAndDispatchesEvent(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn(null);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $passwordHasher = new PasswordHasher();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserRegistered::class));

        $service = new RegisterUserService(
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $entityManager,
            $eventDispatcher,
            $logger
        );

        $service->register('test-login', 'password', 'test@example.com');
    }

    public function testRegisterWithDuplicateEmail(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn(new User('login', 'pass', 'test@example.com', 'en-US', 'UTC'));

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $passwordHasher = new PasswordHasher();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new RegisterUserService(
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $entityManager,
            $eventDispatcher,
            $logger
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User with this email already exists.');

        $service->register('test-login', 'password', 'test@example.com');
    }

    public function testRegisterRollsBackTransactionOnError(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn(null);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('save')->willThrowException(new Exception('Database error'));

        $passwordHasher = new PasswordHasher();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->never())->method('run');
        $eventDispatcher->expects($this->never())->method('dispatch');

        $service = new RegisterUserService(
            $userRepository,
            $tokenRepository,
            $passwordHasher,
            $entityManager,
            $eventDispatcher,
            $logger
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database error');

        $service->register('test-login', 'password', 'test@example.com');
    }
}
