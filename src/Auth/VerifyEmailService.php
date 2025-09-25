<?php

declare(strict_types=1);

namespace App\Auth;

use App\Auth\Exception\InvalidTokenException;
use App\Auth\Exception\TokenExpiredException;
use App\Auth\Exception\TokenUsedException;
use Cycle\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class VerifyEmailService
{
    public function __construct(
        private EmailVerificationTokenRepositoryInterface $tokenRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws InvalidTokenException
     * @throws TokenExpiredException
     * @throws TokenUsedException
     * @throws Throwable
     */
    public function verify(string $rawToken): void
    {
        $this->logger->info('Attempting to verify email with a token.');

        $tokenHash = hash('sha256', $rawToken);
        $token = $this->tokenRepository->findByHash($tokenHash);

        if ($token === null) {
            $this->logger->warning('Email verification failed: token not found.');
            throw new InvalidTokenException('Invalid verification token.');
        }

        if ($token->isUsed()) {
            $this->logger->warning('Email verification failed: token already used.', ['token_id' => $token->getId()]);
            throw new TokenUsedException('This verification link has already been used.');
        }

        if ($token->isExpired()) {
            $this->logger->warning('Email verification failed: token expired.', ['token_id' => $token->getId()]);
            throw new TokenExpiredException('This verification link has expired.');
        }

        $user = $token->getUser();
        $user->activate();
        $token->markAsUsed();

        $this->entityManager->persist($token);
        $this->entityManager->persist($user);
        $this->entityManager->run();

        $this->logger->info(
            'Successfully verified email for user {user_id}.',
            ['user_id' => $user->getId(), 'token_id' => $token->getId()]
        );
    }
}
