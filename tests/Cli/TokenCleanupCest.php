<?php

declare(strict_types=1);

namespace App\Tests\Cli;

use App\Tests\Support\CliTester;
use DateTimeImmutable;

final class TokenCleanupCest
{
    private const string TABLE_NAME = 'email_verification_token';

    public function _before(CliTester $I): void
    {
        // Ensure a user with a unique ID exists for the foreign key constraint
        $I->haveInDatabase('user', [
            'id' => 99,
            'login' => 'test-user',
            'email' => 'test@example.com',
            'password_hash' => 'hash',
            'created_at' => '2025-09-24 10:00:00',
            'updated_at' => '2025-09-24 10:00:00',
            'token' => 'dummy-token',
            'status' => 1, // Pending
        ]);
    }

    public function cleanupWithNoTokens(CliTester $I): void
    {
        $I->runShellCommand('php yii token:cleanup');
        $I->seeInShellOutput('No expired or used tokens to delete');
        $I->seeResultCodeIs(0);
    }

    public function cleanupWithMixedTokens(CliTester $I): void
    {
        $now = new DateTimeImmutable();

        // 1. Seed the database with tokens
        $I->haveInDatabase(self::TABLE_NAME, [
            'user_id' => 99,
            'token_hash' => 'expired_token_hash',
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->modify('-1 day')->format('Y-m-d H:i:s'), // Expired
        ]);

        $I->haveInDatabase(self::TABLE_NAME, [
            'user_id' => 99,
            'token_hash' => 'used_token_hash',
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->modify('+1 day')->format('Y-m-d H:i:s'),
            'used_at' => $now->modify('-1 hour')->format('Y-m-d H:i:s'), // Used
        ]);

        $I->haveInDatabase(self::TABLE_NAME, [
            'user_id' => 99,
            'token_hash' => 'valid_token_hash',
            'created_at' => $now->format('Y-m-d H:i:s'),
            'expires_at' => $now->modify('+1 day')->format('Y-m-d H:i:s'), // Valid
        ]);

        $I->seeNumRecords(3, self::TABLE_NAME);

        // 2. Run the command
        $I->runShellCommand('php yii token:cleanup');

        // 3. Assert the output and database state
        $I->seeInShellOutput('Deleted 2 expired/used tokens');
        $I->seeResultCodeIs(0);

        $I->seeNumRecords(1, self::TABLE_NAME);
        $I->seeInDatabase(self::TABLE_NAME, ['token_hash' => 'valid_token_hash']);
        $I->dontSeeInDatabase(self::TABLE_NAME, ['token_hash' => 'expired_token_hash']);
        $I->dontSeeInDatabase(self::TABLE_NAME, ['token_hash' => 'used_token_hash']);
    }
}
