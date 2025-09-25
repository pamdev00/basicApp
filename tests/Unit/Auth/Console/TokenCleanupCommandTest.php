<?php

declare(strict_types=1);

namespace App\Tests\Unit\Auth\Console;

use App\Auth\Console\TokenCleanupCommand;
use App\Auth\EmailVerificationToken;
use App\Auth\EmailVerificationTokenRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TokenCleanupCommandTest extends TestCase
{
    public function testExecuteWithTokensToDelete(): void
    {
        $token1 = $this->createMock(EmailVerificationToken::class);
        $token2 = $this->createMock(EmailVerificationToken::class);
        $tokens = [$token1, $token2];

        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('findAllExpiredOrUsed')->willReturn($tokens);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))->method('delete');
        $entityManager->expects($this->once())->method('run');

        $command = new TokenCleanupCommand($tokenRepository, $entityManager);

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $reflection = new \ReflectionObject($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $input, $output);

        $this->assertSame(0, $result);
    }

    public function testExecuteWithNoTokensToDelete(): void
    {
        $tokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $tokenRepository->method('findAllExpiredOrUsed')->willReturn([]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('delete');
        $entityManager->expects($this->never())->method('run');

        $command = new TokenCleanupCommand($tokenRepository, $entityManager);

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $reflection = new \ReflectionObject($command);
        $method = $reflection->getMethod('execute');
        $method->setAccessible(true);

        $result = $method->invoke($command, $input, $output);

        $this->assertSame(0, $result);
    }
}
