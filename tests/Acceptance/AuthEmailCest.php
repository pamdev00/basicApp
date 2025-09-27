<?php

declare(strict_types=1);

namespace Acceptance;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

final class AuthEmailCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->cleanDir($I->getRateLimiterCachePath());
    }


    public function verifyEmailSuccess(AcceptanceTester $I): void
    {
        $email = 'verify-success@example.com';
        $mailDir = 'runtime/mail';

        // 1. Clean mail directory
        $I->cleanDir($mailDir);

        // 2. Register a new user
        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => 'verify-success',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // 3. Grab email content using the new helper
        $mailContent = $I->grabLatestEmailContent($mailDir);

        $I->comment($mailContent);

        // 4. Extract token from email
        preg_match('#/verify-email/([a-zA-Z0-9_\-]+)#', $mailContent, $matches);
        $token = $matches[1] ?? null;

        $I->assertNotNull($token, 'Token not found in email');

        // 5. Send verification request
        $I->sendGET('/verify-email/' . $token);

        // 6. Assert success
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['status' => 'success']);

        // 7. Check user status in DB
        $I->seeInDatabase('user', ['email' => $email, 'status' => 2]); // 2 = ACTIVE
    }

    public function verifyEmailWithInvalidToken(AcceptanceTester $I): void
    {
        $I->sendGET('/verify-email/invalid-token');
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson([
            'title' => 'Invalid Verification Token',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'detail' => 'Invalid verification token.',
        ]);
    }

    public function verifyEmailWithNoToken(AcceptanceTester $I): void
    {
        $I->sendGET('/verify-email/');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function resendVerificationSuccess(AcceptanceTester $I): void
    {
        // 1. Create an unverified user
        $email = 'unverified@example.com';
        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => 'unverified',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // 2. Clean mail directory
        $mailDir = 'runtime/mail';
        $I->cleanDir($mailDir);

        // 3. Resend verification email
        $I->sendPOST('/resend-verification', ['email' => $email]);
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // 4. Assert that a new email is sent
        $mailPath = $I->getMailDirectoryPath();
        $files = glob($mailPath . '/*.eml');
        $I->assertNotEmpty($files, 'No email files found.');
        $I->seeFileFound($files[0]);
    }

    public function resendVerificationWithNonExistentEmail(AcceptanceTester $I): void
    {
        $I->sendPOST('/resend-verification', ['email' => 'non-existent@example.com']);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function resendVerificationWithActiveUser(AcceptanceTester $I): void
    {
        // 1. Create and verify a user
        $email = 'active-user@example.com';
        $mailDir = 'runtime/mail';
        $I->cleanDir($mailDir);
        $I->sendPOST('/register/', ['email' => $email, 'login' => 'active-user', 'password' => 'password123', 'password_confirm' => 'password123']);
        $mailContent = $I->grabLatestEmailContent($mailDir);
        preg_match('#/verify-email/([a-zA-Z0-9_\\-]+)#', $mailContent, $matches);
        $token = $matches[1] ?? null;
        $I->sendGET('/verify-email/' . $token);
        $I->seeResponseCodeIs(HttpCode::OK);

        // 2. Clean mail directory again
        $I->cleanDir($mailDir);

        // 3. Try to resend verification
        $I->sendPOST('/resend-verification', ['email' => $email]);
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // 4. Assert that no new email is sent
        $mailPath = $I->getMailDirectoryPath();
        $I->assertEmpty(glob($mailPath . '/*.eml'), 'Email was sent but should not have been.');
    }
}
