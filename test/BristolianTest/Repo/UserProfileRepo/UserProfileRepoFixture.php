<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Types\UserProfileWithDisplayName;
use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for UserProfileRepo implementations.
 *
 * @coversNothing
 */
abstract class UserProfileRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the UserProfileRepo implementation.
     *
     * @return UserProfileRepo
     */
    abstract public function getTestInstance(): UserProfileRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user-123';
    }

//    /**
//     * @covers \Bristolian\Repo\UserProfileRepo\UserProfileRepo::getUserProfile
//     */
//    public function test_getUserProfile_returns_profile_even_for_nonexistent_user(): void
//    {
//        $repo = $this->getTestInstance();
//
//        $user_id = 'nonexistent-user-id-' . uniqid();
//
//        $result = $repo->getUserProfile($user_id);
//
//        $this->assertInstanceOf(UserProfileWithDisplayName::class, $result);
//        $this->assertSame($user_id, $result->getUserId());
//        $this->assertNull($result->getAvatarImageId());
//        $this->assertNull($result->getAboutMe());
//        $this->assertSame('', $result->getDisplayName()); // No display name yet
//    }

    public function test_updateProfile_creates_profile_and_display_name(): void
    {
        $repo = $this->getTestInstance();

        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Test User',
            'about_me' => 'About me text',
        ]));

        $result = $repo->updateProfile($this->getTestUserId(), $params);

        $this->assertInstanceOf(UserProfileWithDisplayName::class, $result);
        $this->assertSame($this->getTestUserId(), $result->getUserId());
        $this->assertSame('Test User', $result->getDisplayName());
        $this->assertSame('About me text', $result->getAboutMe());
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\UserProfileRepo::updateProfile
     */
    public function test_updateProfile_creates_new_display_name_version(): void
    {
        $repo = $this->getTestInstance();

        $params1 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'First Name',
            'about_me' => 'First about me',
        ]));
        $params2 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Second Name',
            'about_me' => 'Second about me',
        ]));

        $user_id = $this->getTestUserId();
        $repo->updateProfile($user_id, $params1);
        $result = $repo->updateProfile($user_id, $params2);

        $this->assertSame('Second Name', $result->getDisplayName());
        $this->assertSame('Second about me', $result->getAboutMe());
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\UserProfileRepo::getDisplayNameHistory
     * @covers \Bristolian\Repo\UserProfileRepo\UserProfileRepo::updateProfile
     */
    public function test_getDisplayNameHistory_returns_all_versions_ordered_desc(): void
    {
        $repo = $this->getTestInstance();

        $params1 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Name 1',
            'about_me' => 'About 1',
        ]));
        $params2 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Name 2',
            'about_me' => 'About 2',
        ]));
        $params3 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Name 3',
            'about_me' => 'About 3',
        ]));

        $user_id = $this->getTestUserId();
        $repo->updateProfile($user_id, $params1);
        $repo->updateProfile($user_id, $params2);
        $repo->updateProfile($user_id, $params3);

        $history = $repo->getDisplayNameHistory($user_id);

        $this->assertCount(3, $history);
        $this->assertContainsOnlyInstancesOf(UserDisplayName::class, $history);
        // Should be ordered by version desc (newest first)
        $this->assertSame('Name 3', $history[0]->display_name);
        $this->assertSame('Name 2', $history[1]->display_name);
        $this->assertSame('Name 1', $history[2]->display_name);
    }

    public function test_updateAvatarImage_updates_avatar(): void
    {
        $repo = $this->getTestInstance();

        // Should not throw exception
        $repo->updateAvatarImage($this->getTestUserId(), 'avatar-image-id-456');

        $profile = $repo->getUserProfile($this->getTestUserId());
        $this->assertSame('avatar-image-id-456', $profile->getAvatarImageId());
    }

    public function test_updateAvatarImage_updates_existing_profile(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Test User',
            'about_me' => 'About me',
        ]));

        $repo->updateProfile($user_id, $params);
        $repo->updateAvatarImage($user_id, 'new-avatar-id');

        $profile = $repo->getUserProfile($user_id);
        $this->assertSame('new-avatar-id', $profile->getAvatarImageId());
        $this->assertSame('About me', $profile->getAboutMe()); // Should preserve about_me
    }

    public function test_getUserProfile_returns_latest_display_name(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $params1 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Old Name',
            'about_me' => 'About',
        ]));
        $params2 = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'New Name',
            'about_me' => 'About',
        ]));

        $repo->updateProfile($user_id, $params1);
        $repo->updateProfile($user_id, $params2);

        $profile = $repo->getUserProfile($user_id);
        $this->assertSame('New Name', $profile->getDisplayName());
    }
}
