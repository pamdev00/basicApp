<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth;

use App\Auth\EmailVerificationTokenRepositoryInterface;
use App\Auth\ResendVerificationService;
use App\Auth\UserRegistered;
use App\Exception\NotFoundException;
use App\User\Enum\UserStatus;
use App\User\User;
use App\User\UserRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ResendVerificationServiceTest extends TestCase
{
    public function testResendSuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getStatus')->willReturn(UserStatus::WAITING_VERIFICATION);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn($user);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserRegistered::class));

        $service = new ResendVerificationService($userRepository, $tokenRepository, $entityManager, $eventDispatcher);
        $service->resend('test@example.com');
    }

    public function testResendForActiveUserDoesNothing(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getStatus')->willReturn(UserStatus::ACTIVE);

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn($user);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $eventDispatcher->expects($this->never())->method('dispatch');

        $service = new ResendVerificationService($userRepository, $tokenRepository, $entityManager, $eventDispatcher);
        $service->resend('active@example.com');
    }

    public function testResendForNotFoundUserThrowsException(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByEmail')->willReturn(null);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $service = new ResendVerificationService($userRepository, $tokenRepository, $entityManager, $eventDispatcher);

        $this->expectException(NotFoundException::class);
        $service->resend('not-found@example.com');
    }
}
