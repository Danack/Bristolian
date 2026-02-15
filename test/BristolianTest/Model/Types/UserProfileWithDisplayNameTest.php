<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Generated\UserProfile;
use Bristolian\Model\Types\UserProfileWithDisplayName;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UserProfileWithDisplayNameTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\UserProfileWithDisplayName
     */
    public function test_getters_with_display_name(): void
    {
        $now = new \DateTimeImmutable();
        $userProfile = new UserProfile(
            user_id: 'user-123',
            avatar_image_id: 'avatar-456',
            about_me: 'About me text',
            created_at: $now,
            updated_at: $now
        );
        $displayName = new UserDisplayName(
            id: 1,
            user_id: 'user-123',
            display_name: 'Test User',
            version: 1,
            created_at: $now
        );

        $profile = new UserProfileWithDisplayName($userProfile, $displayName);

        $this->assertSame('user-123', $profile->getUserId());
        $this->assertSame('Test User', $profile->getDisplayName());
        $this->assertSame('About me text', $profile->getAboutMe());
        $this->assertSame('avatar-456', $profile->getAvatarImageId());
    }

    /**
     * @covers \Bristolian\Model\Types\UserProfileWithDisplayName
     */
    public function test_getDisplayName_returns_empty_string_when_display_name_null(): void
    {
        $now = new \DateTimeImmutable();
        $userProfile = new UserProfile(
            user_id: 'user-123',
            avatar_image_id: null,
            about_me: null,
            created_at: $now,
            updated_at: $now
        );

        $profile = new UserProfileWithDisplayName($userProfile, null);

        $this->assertSame('user-123', $profile->getUserId());
        $this->assertSame('', $profile->getDisplayName());
        $this->assertNull($profile->getAboutMe());
        $this->assertNull($profile->getAvatarImageId());
    }
}
