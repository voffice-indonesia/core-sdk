<?php

namespace VoxDev\Core\Tests\Unit\Domain\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use VoxDev\Core\Domain\Entities\User;
use VoxDev\Core\Domain\ValueObjects\Email;
use VoxDev\Core\Domain\ValueObjects\UserId;
use VoxDev\Core\Domain\ValueObjects\UserName;

class UserTest extends TestCase
{
    #[Test]
    public function it_creates_user_with_required_fields(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com')
        );

        $this->assertEquals(1, $user->getId()->getValue());
        $this->assertEquals('John Doe', $user->getName()->getValue());
        $this->assertEquals('john@example.com', $user->getEmail()->getValue());
        $this->assertNull($user->getAvatar());
        $this->assertEmpty($user->getAttributes());
    }

    #[Test]
    public function it_creates_user_with_optional_fields(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com'),
            'https://example.com/avatar.jpg',
            ['role' => 'admin', 'active' => true]
        );

        $this->assertEquals('https://example.com/avatar.jpg', $user->getAvatar());
        $this->assertEquals(['role' => 'admin', 'active' => true], $user->getAttributes());
    }

    #[Test]
    public function it_handles_attributes(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com'),
            null,
            ['role' => 'admin']
        );

        $this->assertEquals('admin', $user->getAttribute('role'));
        $this->assertNull($user->getAttribute('nonexistent'));
        $this->assertTrue($user->hasAttribute('role'));
        $this->assertFalse($user->hasAttribute('nonexistent'));
    }

    #[Test]
    public function it_adds_attributes_immutably(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com')
        );

        $newUser = $user->withAttribute('role', 'admin');

        $this->assertFalse($user->hasAttribute('role'));
        $this->assertTrue($newUser->hasAttribute('role'));
        $this->assertEquals('admin', $newUser->getAttribute('role'));
    }

    #[Test]
    public function it_removes_attributes_immutably(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com'),
            null,
            ['role' => 'admin', 'active' => true]
        );

        $newUser = $user->withoutAttribute('role');

        $this->assertTrue($user->hasAttribute('role'));
        $this->assertFalse($newUser->hasAttribute('role'));
        $this->assertTrue($newUser->hasAttribute('active'));
    }

    #[Test]
    public function it_compares_users(): void
    {
        $user1 = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com')
        );

        $user2 = new User(
            UserId::fromValue(1),
            UserName::fromValue('Jane Doe'),
            Email::fromValue('jane@example.com')
        );

        $user3 = new User(
            UserId::fromValue(2),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com')
        );

        $this->assertTrue($user1->isEqual($user2)); // Same ID
        $this->assertFalse($user1->isEqual($user3)); // Different ID
    }

    #[Test]
    public function it_converts_to_array(): void
    {
        $user = new User(
            UserId::fromValue(1),
            UserName::fromValue('John Doe'),
            Email::fromValue('john@example.com'),
            'https://example.com/avatar.jpg',
            ['role' => 'admin']
        );

        $expected = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
            'attributes' => ['role' => 'admin'],
        ];

        $this->assertEquals($expected, $user->toArray());
    }

    #[Test]
    public function it_creates_from_array(): void
    {
        $data = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
            'attributes' => ['role' => 'admin'],
        ];

        $user = User::fromArray($data);

        $this->assertEquals(1, $user->getId()->getValue());
        $this->assertEquals('John Doe', $user->getName()->getValue());
        $this->assertEquals('john@example.com', $user->getEmail()->getValue());
        $this->assertEquals('https://example.com/avatar.jpg', $user->getAvatar());
        $this->assertEquals(['role' => 'admin'], $user->getAttributes());
    }
}
