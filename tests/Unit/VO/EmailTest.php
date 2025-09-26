<?php

declare(strict_types=1);

namespace App\Tests\Unit\VO;

use App\VO\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testFromStringWithValidEmail(): void
    {
        $email = Email::fromString('test@example.com');
        $this->assertInstanceOf(Email::class, $email);
    }

    public function testFromStringWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        Email::fromString('invalid-email');
    }

    public function testToString(): void
    {
        $emailString = 'test@example.com';
        $email = Email::fromString($emailString);
        $this->assertSame($emailString, (string)$email);
    }
}
