<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\AdminUser;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class AdminUserTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\AdminUser
     */
    public function testNew()
    {
        $userId = 'test-user-id';
        $emailAddress = 'test@example.com';
        $passwordHash = 'hashed-password';

        $adminUser = AdminUser::new($userId, $emailAddress, $passwordHash);

        $this->assertSame($userId, $adminUser->getUserId());
        $this->assertSame($emailAddress, $adminUser->getEmailAddress());
        $this->assertSame($passwordHash, $adminUser->getPasswordHash());
    }

    /**
     * @covers \Bristolian\Model\Types\AdminUser
     */
    public function testFromPartial()
    {
        $emailAddress = 'partial@example.com';
        $passwordHash = 'hashed-password-partial';

        $adminUser = AdminUser::fromPartial($emailAddress, $passwordHash);

        $this->assertSame($emailAddress, $adminUser->getEmailAddress());
        $this->assertSame($passwordHash, $adminUser->getPasswordHash());
    }

    /**
     * @covers \Bristolian\Model\Types\AdminUser
     */
    public function testToArray()
    {
        $adminUser = AdminUser::new('user-123', 'test@example.com', 'hash123');
        $array = $adminUser->toArray();

        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('email_address', $array);
        $this->assertArrayHasKey('password_hash', $array);
    }
}

