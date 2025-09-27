<?php

declare(strict_types=1);

namespace App\Tests\Functional\User;

use App\User\User;
use App\User\UserRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Testing\FunctionalTester;

final class UserRepositoryTest extends TestCase
{
    private ?FunctionalTester $tester;
    private ?UserRepositoryInterface $repository;
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->tester = new FunctionalTester();
        $this->tester->bootstrapApplication(dirname(__DIR__, 3));
        $container = $this->tester->getContainer();
        $this->repository = $container->get(UserRepositoryInterface::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        // Clean up the created user
        $user = $this->repository->findByEmail('test@example.com');
        if ($user !== null) {
            $this->entityManager->delete($user);
            $this->entityManager->run();
        }
    }

    public function testSaveAndFindByEmail(): void
    {
        $user = new User(
            'test-login',
            'password',
            'test@example.com',
            'en-US',
            'UTC'
        );

        $this->entityManager->persist($user);
        $this->entityManager->run();

        $foundUser = $this->repository->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertSame('test-login', $foundUser->getLogin());
        $this->assertSame('test@example.com', $foundUser->getEmail());
    }

    public function testFindByEmailWithNonExistentUser(): void
    {
        $user = $this->repository->findByEmail('non-existent@example.com');
        $this->assertNull($user);
    }
}
