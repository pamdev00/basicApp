<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

final class AuthScenariosCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->cleanDir($I->getRateLimiterCachePath());
    }

    public function testRegisterRateLimiting(AcceptanceTester $I): void
    {
        $I->wantTo('Check rate limiting for registration');
        $email = 'rate-limit-test@example.com';
        $login = 'rate-limit-test';

        // 1st request
        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => $login,
                'password' => 'password',
                'password_confirm' => 'password',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // Send 6 more requests to hit the limit of 7.
        for ($i = 0; $i < 6; $i++) {
            $I->sendPOST(
                '/register/',
                [
                    'email' => $email,
                    'login' => $login,
                    'password' => 'password',
                    'password_confirm' => 'password',
                ]
            );
        }

        // The 8th request must be rate limited.
        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => $login,
                'password' => 'password',
                'password_confirm' => 'password',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::TOO_MANY_REQUESTS);
    }

    public function testResendVerificationRateLimiting(AcceptanceTester $I): void
    {
        $I->wantTo('Check rate limiting for resend verification');
        $email = 'resend-limit@example.com';

        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => 'resend-limit',
                'password' => 'password',
                'password_confirm' => 'password',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        // 1 (register) + 6 (resend) = 7 successful requests
        for ($i = 0; $i < 6; $i++) {
            $I->sendPOST('/resend-verification', ['email' => $email]);
            $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        }

        // 8th request should be blocked
        $I->sendPOST('/resend-verification', ['email' => $email]);
        $I->seeResponseCodeIs(HttpCode::TOO_MANY_REQUESTS);
    }

    public function testLoginWithUnverifiedEmail(AcceptanceTester $I): void
    {
        $I->wantTo('Try to login with an unverified email');
        $email = 'unverified-login@example.com';
        $password = 'password123';

        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => 'unverified-login',
                'password' => $password,
                'password_confirm' => $password,
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        $I->sendPOST(
            '/auth/',
            [
                'login' => 'unverified-login',
                'password' => $password,
            ]
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['detail' => 'Invalid password.']);
    }

    public function testLoginWithWrongPassword(AcceptanceTester $I): void
    {
        $I->wantTo('Try to login with a wrong password');
        $email = 'verified-user@example.com';
        $password = 'password123';
        $mailDir = 'runtime/mail';

        $I->cleanDir($mailDir);
        $I->sendPOST('/register/', ['email' => $email, 'login' => 'verified-user', 'password' => $password, 'password_confirm' => $password]);
        $mailContent = $I->grabLatestEmailContent($mailDir);
        preg_match('#/verify-email/([a-zA-Z0-9_\-]+)#', $mailContent, $matches);
        $token = $matches[1] ?? null;
        $I->sendGET('/verify-email/' . $token);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendPOST(
            '/auth/',
            [
                'login' => 'verified-user',
                'password' => 'wrong-password',
            ]
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['detail' => 'Invalid password.']);
    }

    public function testRegisterWithExistingLogin(AcceptanceTester $I): void
    {
        $I->wantTo('Try to register with an already existing login');
        $I->sendPOST(
            '/register/',
            [
                'email' => 'user1@example.com',
                'login' => 'existing-login',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        $I->sendPOST(
            '/register/',
            [
                'email' => 'user2@example.com',
                'login' => 'existing-login',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::CONFLICT);
    }

    public function testIdempotentEmailVerification(AcceptanceTester $I): void
    {
        $I->wantTo('Check that email verification is idempotent');
        $email = 'idempotent@example.com';
        $mailDir = 'runtime/mail';

        $I->cleanDir($mailDir);
        $I->sendPOST('/register/', ['email' => $email, 'login' => 'idempotent', 'password' => 'password123', 'password_confirm' => 'password123']);
        $mailContent = $I->grabLatestEmailContent($mailDir);
        preg_match('#/verify-email/([a-zA-Z0-9_\-]+)#', $mailContent, $matches);
        $token = $matches[1] ?? null;

        $I->assertNotNull($token, 'Token not found in email');

        // First verification
        $I->sendGET('/verify-email/' . $token);
        $I->seeResponseCodeIs(HttpCode::OK);

        // Second verification should fail, as the token is already used
        $I->sendGET('/verify-email/' . $token);
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson(['detail' => 'This verification link has already been used.']);
    }

    public function testVerifyWithExpiredToken(AcceptanceTester $I): void
    {
        $I->wantTo('Try to verify email with an expired token');
        $email = 'expired-token@example.com';
        $mailDir = 'runtime/mail';

        $I->cleanDir($mailDir);
        $I->sendPOST('/register/', ['email' => $email, 'login' => 'expired-token', 'password' => 'password123', 'password_confirm' => 'password123']);
        $mailContent = $I->grabLatestEmailContent($mailDir);
        preg_match('#/verify-email/([a-zA-Z0-9_\-]+)#', $mailContent, $matches);
        $token = $matches[1] ?? null;

        $I->assertNotNull($token, 'Token not found in email');

        // Manually expire the token in the database
        $userId = $I->grabFromDatabase('user', 'id', ['email' => $email]);
        $I->updateInDatabase('email_verification_token', ['expires_at' => '2000-01-01 00:00:00'], ['user_id' => $userId]);

        $I->sendGET('/verify-email/' . $token);
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContainsJson(['detail' => 'This verification link has expired.']);
    }
}
