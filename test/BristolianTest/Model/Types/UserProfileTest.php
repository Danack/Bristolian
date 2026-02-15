<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\UserProfile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UserProfileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\UserProfile
     */
    public function test_construct(): void
    {
        $userId = 'user-123';
        $avatarImageId = 'avatar-456';
        $aboutMe = 'About me text';
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable('2024-02-15');

        $profile = new UserProfile($userId, $avatarImageId, $aboutMe, $createdAt, $updatedAt);

        $this->assertSame($userId, $profile->user_id);
        $this->assertSame($avatarImageId, $profile->avatar_image_id);
        $this->assertSame($aboutMe, $profile->about_me);
        $this->assertSame($createdAt, $profile->created_at);
        $this->assertSame($updatedAt, $profile->updated_at);
    }
}
