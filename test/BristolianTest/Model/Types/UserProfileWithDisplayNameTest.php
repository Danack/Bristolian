<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use BristolianGenerated\Model\UserDisplayName;
use BristolianGenerated\Model\UserProfile;
use Bristolian\Model\Types\UserProfileWithDisplayName;
use BristolianTest\BaseTestCase;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Model\Types\UserProfileWithDisplayName::class)]

class UserProfileWithDisplayNameTest extends BaseTestCase
{
    public function test_getters_with_display_name(): void
    {
        $now = new DateTimeImmutable();
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

    public function test_getDisplayName_returns_empty_string_when_display_name_null(): void
    {
        $now = new DateTimeImmutable();
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
