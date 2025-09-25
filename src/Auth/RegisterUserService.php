<?php

declare(strict_types=1);

namespace App\Auth;

use App\User\User;
use App\User\UserRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Security\PasswordHasher;
use Yiisoft\Security\Random;

final readonly class RegisterUserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailVerificationTokenRepositoryInterface $tokenRepository,
        private PasswordHasher $passwordHasher,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws Exception
     */
    public function register(string $login, string $password, string $email): void
    {
        if ($this->userRepository->findByEmail($email) !== null) {
            // TODO: use specific exception
            throw new Exception('User with this email already exists.');
        }
        // todo нужно получать эти данные из запроса
        $locale = 'ru-RU';
        $timezone = 'Paris';

        $user = new User($login, $password, $email, $locale, $timezone);
        $user->setPassword($this->passwordHasher->hash($password));

        $rawToken = Random::string(64);
        $tokenHash = hash('sha256', $rawToken);

        $token = new EmailVerificationToken(
            $tokenHash,
            (new DateTimeImmutable())->modify('+1 day'),
            $user
        );

        $this->entityManager->persist($user);
        $this->tokenRepository->save($token);
        $this->entityManager->run();

        $this->logger->info('Dispatching UserRegistered event.');
        $this->eventDispatcher->dispatch(new UserRegistered($user, $rawToken));
        $this->logger->info('UserRegistered event dispatched.');
    }
}
