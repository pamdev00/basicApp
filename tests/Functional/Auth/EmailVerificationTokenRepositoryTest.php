<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Auth\EmailVerificationToken;
use App\Auth\EmailVerificationTokenRepositoryInterface;
use App\User\User;
use App\User\UserRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Testing\FunctionalTester;

final class EmailVerificationTokenRepositoryTest extends TestCase
{
    private ?FunctionalTester $tester;
    private ?EmailVerificationTokenRepositoryInterface $tokenRepository;
    private ?UserRepositoryInterface $userRepository;
    private ?EntityManagerInterface $entityManager;
    private ?User $user = null;

    protected function setUp(): void
    {
        $this->tester = new FunctionalTester();
        $this->tester->bootstrapApplication(dirname(__DIR__, 3));

        $container = $this->tester->getContainer();
        $this->tokenRepository = $container->get(EmailVerificationTokenRepositoryInterface::class);
        $this->userRepository = $container->get(UserRepositoryInterface::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        if ($this->user !== null) {
            $user = $this->userRepository->findByEmail($this->user->getEmail());
            if ($user !== null) {
                // Tokens are deleted via cascade
                $this->entityManager->delete($user);
                $this->entityManager->run();
            }
        }
    }

    public function testSaveAndFindByHash(): void
    {
        // 1. Create a user to associate the token with
        $this->user = new User('token-user', 'password', 'token-user@example.com');
        $this->entityManager->persist($this->user);
        $this->entityManager->run();

        // 2. Create and save a token
        $rawToken = 'test-token-123';
        $hash = hash('sha256', $rawToken);
        $expires = (new DateTimeImmutable())->modify('+1 day');
        $token = new EmailVerificationToken($hash, $expires, $this->user);

        $this->tokenRepository->save($token);
        $this->entityManager->run();

        // 3. Find the token by its hash
        $foundToken = $this->tokenRepository->findByHash($hash);

        // 4. Assertions
        $this->assertInstanceOf(EmailVerificationToken::class, $foundToken);
        $this->assertSame($hash, $foundToken->getTokenHash());
        $this->assertSame($this->user->getId(), $foundToken->getUser()->getId());
    }

    public function testFindAllExpiredOrUsed(): void
    {
        // 1. Create a user
        $this->user = new User('token-user-2', 'password', 'token-user-2@example.com');
        $this->entityManager->persist($this->user);
        $this->entityManager->run();

        // 2. Create a mix of tokens
        $now = new DateTimeImmutable();

        // Token 1: Expired
        $expiredToken = new EmailVerificationToken(hash('sha256', 'expired'), $now->modify('-1 day'), $this->user);
        $this->tokenRepository->save($expiredToken);

        // Token 2: Used
        $usedToken = new EmailVerificationToken(hash('sha256', 'used'), $now->modify('+1 day'), $this->user);
        $usedToken->markAsUsed();
        $this->tokenRepository->save($usedToken);

        // Token 3: Valid
        $validToken = new EmailVerificationToken(hash('sha256', 'valid'), $now->modify('+1 day'), $this->user);
        $this->tokenRepository->save($validToken);

        $this->entityManager->run();

        // 3. Find expired or used tokens
        $results = $this->tokenRepository->findAllExpiredOrUsed();
        $resultHashes = [];
        foreach ($results as $result) {
            $resultHashes[] = $result->getTokenHash();
        }

        // 4. Assertions
        $this->assertCount(2, $results);
        $this->assertContains($expiredToken->getTokenHash(), $resultHashes);
        $this->assertContains($usedToken->getTokenHash(), $resultHashes);
        $this->assertNotContains($validToken->getTokenHash(), $resultHashes);
    }
}
