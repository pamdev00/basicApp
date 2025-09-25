<?php

declare(strict_types=1);

namespace App\Auth;

use App\Exception\NotFoundException;
use App\User\UserRepositoryInterface;
use App\User\Enum\UserStatus;
use Cycle\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Security\Random;

final readonly class ResendVerificationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailVerificationTokenRepositoryInterface $tokenRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function resend(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new NotFoundException('User not found.');
        }

        if ($user->getStatus() === UserStatus::ACTIVE) {
            // Or maybe throw a specific exception?
            // For now, we just do nothing if user is already active.
            return;
        }

        // Create a new token
        $rawToken = Random::string(64);
        $tokenHash = hash('sha256', $rawToken);

        $token = new EmailVerificationToken(
            $tokenHash,
            (new DateTimeImmutable())->modify('+1 day'),
            $user
        );

        $this->tokenRepository->save($token);
        $this->entityManager->run();

        // Dispatch the same event as registration
        $this->eventDispatcher->dispatch(new UserRegistered($user, $rawToken));
    }
}
