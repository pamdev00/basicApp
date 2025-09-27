<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;
use Yiisoft\Json\Json;

final class AuthCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->cleanDir($I->getRateLimiterCachePath());
    }

    public function testAuth(AcceptanceTester $I): void
    {
        $I->sendPOST(
            '/auth/',
            [
                'login' => 'Opal1144',
                'password' => 'Opal1144',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'success',
                'error_message' => '',
                'error_code' => null,
            ]
        );

        $response = Json::decode($I->grabResponse());
        $I->seeInDatabase(
            'user',
            [
                'id' => 1,
                'token' => $response['data']['token'],
            ]
        );
    }

    public function logout(AcceptanceTester $I): void
    {
        $I->haveHttpHeader(
            'X-Api-Key',
            'lev1ZsWCzqrMlXRI2sT8h4ApYpSgBMl1xf6D4bCRtiKtDqw6JN36yLznargilQ_rEJz9zTfcUxm53PLODCToF9gGin38Rd4NkhQPOVeH5VvZvBaQlUg64E6icNCubiAv'
        );

        $I->sendPOST(
            '/logout/'
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'status' => 'success',
                'error_message' => '',
                'error_code' => null,
            ]
        );

        $I->dontSeeInDatabase(
            'user',
            [
                'id' => 1,
                'token' => 'lev1ZsWCzqrMlXRI2sT8h4ApYpSgBMl1xf6D4bCRtiKtDqw6JN36yLznargilQ_rEJz9zTfcUxm53PLODCToF9gGin38Rd4NkhQPOVeH5VvZvBaQlUg64E6icNCubiAv',
            ]
        );
    }

    public function logoutWithBadToken(AcceptanceTester $I): void
    {
        $I->haveHttpHeader(
            'X-Api-Key',
            'bad-token'
        );

        $I->haveHttpHeader(
            'Accept',
            'application/json'
        );

        $I->sendPOST(
            '/logout/'
        );

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'title' => 'Application Error',
                'status' => HttpCode::UNAUTHORIZED,
                'detail' => 'Unauthorised request',
            ]
        );
    }

    public function testRegisterSuccess(AcceptanceTester $I): void
    {
        $email = 'test-user@example.com';
        $login = 'test-user';
        $password = 'password123';

        $I->sendPOST(
            '/register/',
            [
                'email' => $email,
                'login' => $login,
                'password' => $password,
                'password_confirm' => $password,
            ]
        );

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'verification_status' => 'pending',
                'message' => 'Verification email sent.',
            ]
        );

        $I->seeInDatabase(
            'user',
            [
                'email' => $email,
                'login' => $login,
                'status' => 1, // WAITING_VERIFICATION
            ]
        );

        $I->seeInDatabase(
            'email_verification_token',
            [
                'user_id' => $I->grabFromDatabase('user', 'id', ['email' => $email]),
            ]
        );
    }

    public function testRegisterValidationError(AcceptanceTester $I): void
    {
        // Mismatched passwords
        $I->sendPOST(
            '/register/',
            [
                'email' => 'test-user2@example.com',
                'login' => 'test-user2',
                'password' => 'password123',
                'password_confirm' => 'password456',
            ]
        );

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'detail' => 'One or more validation errors occurred.',
            'errors' => [
                'passwordConfirm' => ['PasswordConfirm must be equal to "password".'],
            ],
        ]);

        // Invalid email
        $I->sendPOST(
            '/register/',
            [
                'email' => 'not-an-email',
                'login' => 'test-user2',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'title' => 'Validation Failed',
            'status' => HttpCode::UNPROCESSABLE_ENTITY,
            'detail' => 'One or more validation errors occurred.',
            'errors' => [
                'email' => ['Email is not a valid email address.'],
            ],
        ]);
    }

    public function testRegisterEmailConflict(AcceptanceTester $I): void
    {
        $I->sendPOST(
            '/register/',
            [
                'email' => 'conflict-user@example.com',
                'login' => 'conflict-user',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::ACCEPTED);

        $I->sendPOST(
            '/register/',
            [
                'email' => 'conflict-user@example.com',
                'login' => 'conflict-user',
                'password' => 'password123',
                'password_confirm' => 'password123',
            ]
        );
        $I->seeResponseCodeIs(HttpCode::CONFLICT);
    }


}
