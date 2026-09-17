<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\UserDisplayName;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Model\Types\UserDisplayName::class)]

class UserDisplayNameTest extends BaseTestCase
{
    public function test_construct(): void
    {
        $id = 1;
        $userId = 'user-123';
        $displayName = 'Test User';
        $version = 2;
        $createdAt = new DateTimeImmutable();

        $userDisplayName = new UserDisplayName($id, $userId, $displayName, $version, $createdAt);

        $this->assertSame($id, $userDisplayName->id);
        $this->assertSame($userId, $userDisplayName->user_id);
        $this->assertSame($displayName, $userDisplayName->display_name);
        $this->assertSame($version, $userDisplayName->version);
        $this->assertSame($createdAt, $userDisplayName->created_at);
    }
}
