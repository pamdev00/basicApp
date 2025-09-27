<?php

declare(strict_types=1);

namespace App\Tests\Unit\VO;

use App\VO\PasswordHash;
use PHPUnit\Framework\TestCase;

final class PasswordHashTest extends TestCase
{
    public function testFromPlain(): void
    {
        $password = 'plain-password';
        $hash = PasswordHash::fromPlain($password);
        $this->assertInstanceOf(PasswordHash::class, $hash);
        $this->assertTrue($hash->verify($password));
    }

    public function testVerifyWithCorrectPassword(): void
    {
        $password = 'plain-password';
        $hash = PasswordHash::fromPlain($password);
        $this->assertTrue($hash->verify($password));
    }

    public function testVerifyWithIncorrectPassword(): void
    {
        $password = 'plain-password';
        $hash = PasswordHash::fromPlain($password);
        $this->assertFalse($hash->verify('incorrect-password'));
    }

    public function testFromHash(): void
    {
        $password = 'plain-password';
        $hashString = password_hash($password, PASSWORD_ARGON2ID);
        $hash = PasswordHash::fromHash($hashString);
        $this->assertInstanceOf(PasswordHash::class, $hash);
        $this->assertTrue($hash->verify($password));
    }

    public function testToString(): void
    {
        $password = 'plain-password';
        $hashString = password_hash($password, PASSWORD_ARGON2ID);
        $hash = PasswordHash::fromHash($hashString);
        $this->assertSame($hashString, (string)$hash);
    }
}
