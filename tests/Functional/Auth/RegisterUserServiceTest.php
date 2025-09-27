<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Auth\EmailVerificationTokenRepositoryInterface;
use App\Auth\RegisterUserService;
use App\User\UserRepositoryInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Yiisoft\Yii\Testing\FunctionalTester;

final class RegisterUserServiceTest extends TestCase
{
    private ?FunctionalTester $tester;

    protected function setUp(): void
    {
        $this->tester = new FunctionalTester();
        $this->tester->bootstrapApplication(dirname(__DIR__, 3));
    }

    public function testTransactionIsRolledBackOnFailure(): void
    {
        // 1. Mock the token repository to throw an exception during save
        $mockTokenRepository = $this->createMock(EmailVerificationTokenRepositoryInterface::class);
        $mockTokenRepository->method('save')->willThrowException(new Exception('Forced DB error'));

        $this->tester->mockService(EmailVerificationTokenRepositoryInterface::class, $mockTokenRepository);

        // 2. Get the services from the container
        $container = $this->tester->getContainer();
        $service = $container->get(RegisterUserService::class);
        $userRepository = $container->get(UserRepositoryInterface::class);

        $email = 'rollback-test@example.com';

        // 3. Attempt to register the user, expecting a failure
        try {
            $service->register('rollback-user', 'password', $email);
        } catch (Exception $e) {
            // Exception is expected
            $this->assertSame('Forced DB error', $e->getMessage());
        }

        // 4. Assert that the user was NOT created in the database
        $user = $userRepository->findByEmail($email);
        $this->assertNull($user, 'User should not be created if token saving fails.');
    }
}
