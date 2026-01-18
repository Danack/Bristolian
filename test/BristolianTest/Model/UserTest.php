<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\User;

/**
 * @coversNothing
 */
class UserTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\User
     */
    public function testConstruct()
    {
        // User constructor takes: id (string), created_at (DateTimeInterface)
        $id = 'test-user-id';
        $createdAt = new \DateTimeImmutable();
        $user = new User($id, $createdAt);

        $this->assertSame($id, $user->id);
        $this->assertSame($createdAt, $user->created_at);
    }
}
