<?php

namespace VoxDev\Core\Tests\Unit\Domain\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use VoxDev\Core\Domain\ValueObjects\Email;

class EmailTest extends TestCase
{
    #[Test]
    public function it_creates_valid_email(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->getValue());
        $this->assertEquals('test@example.com', (string) $email);
    }

    #[Test]
    public function it_normalizes_email_to_lowercase(): void
    {
        $email = new Email('TEST@EXAMPLE.COM');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $email = new Email('  test@example.com  ');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    #[Test]
    public function it_gets_domain(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('example.com', $email->getDomain());
    }

    #[Test]
    public function it_gets_local_part(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test', $email->getLocalPart());
    }

    #[Test]
    public function it_compares_emails(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    #[Test]
    public function it_throws_exception_for_empty_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email cannot be empty');

        new Email('');
    }

    #[Test]
    public function it_throws_exception_for_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new Email('invalid-email');
    }

    #[Test]
    public function it_creates_from_value(): void
    {
        $email = Email::fromValue('test@example.com');

        $this->assertInstanceOf(Email::class, $email);
        $this->assertEquals('test@example.com', $email->getValue());
    }
}
