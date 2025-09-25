<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth;

use App\Auth\EmailVerificationToken;
use App\Auth\EmailVerificationTokenRepositoryInterface;
use App\Auth\Exception\InvalidTokenException;
use App\Auth\Exception\TokenExpiredException;
use App\Auth\Exception\TokenUsedException;
use App\Auth\VerifyEmailService;
use App\User\User;
use Cycle\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class VerifyEmailServiceTest extends TestCase
{
    public function testVerifySuccess(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('activate');

        $token = $this->createMock(EmailVerificationToken::class);
        $token->method('isUsed')->willReturn(false);
        $token->method('isExpired')->willReturn(false);
        $token->method('getUser')->willReturn($user);
        $token->expects($this->once())->method('markAsUsed');

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('findByHash')->willReturn($token);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))->method('persist');
        $entityManager->expects($this->once())->method('run');

        $logger = $this->createMock(LoggerInterface::class);

        $service = new VerifyEmailService($tokenRepository, $entityManager, $logger);
        $service->verify('raw-token');
    }

    public function testVerifyWithInvalidToken(): void
    {
        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('findByHash')->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new VerifyEmailService($tokenRepository, $entityManager, $logger);

        $this->expectException(InvalidTokenException::class);
        $service->verify('invalid-token');
    }

    public function testVerifyWithUsedToken(): void
    {
        $token = $this->createMock(EmailVerificationToken::class);
        $token->method('isUsed')->willReturn(true);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('findByHash')->willReturn($token);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new VerifyEmailService($tokenRepository, $entityManager, $logger);

        $this->expectException(TokenUsedException::class);
        $service->verify('used-token');
    }

    public function testVerifyWithExpiredToken(): void
    {
        $token = $this->createMock(EmailVerificationToken::class);
        $token->method('isUsed')->willReturn(false);
        $token->method('isExpired')->willReturn(true);

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('findByHash')->willReturn($token);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new VerifyEmailService($tokenRepository, $entityManager, $logger);

        $this->expectException(TokenExpiredException::class);
        $service->verify('expired-token');
    }
}
