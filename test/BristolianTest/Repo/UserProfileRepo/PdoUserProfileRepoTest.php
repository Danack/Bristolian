<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Model\Types\UserProfileWithDisplayName;
use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoUserProfileRepoTest extends UserProfileRepoFixture
{
    private ?string $cachedTestUserId = null;

    public function getTestInstance(): UserProfileRepo
    {
        return $this->injector->make(PdoUserProfileRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->cachedTestUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->cachedTestUserId = $adminUser->getUserId();
        }
        return $this->cachedTestUserId;
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::__construct
     * @covers \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::getUserProfile
     */
    public function test_pdo_getUserProfile_returns_blank_profile_for_user_with_no_profile_rows(): void
    {
        $adminUser = $this->createTestAdminUser();
        $userId = $adminUser->getUserId();
        $repo = $this->injector->make(PdoUserProfileRepo::class);

        $result = $repo->getUserProfile($userId);

        $this->assertInstanceOf(UserProfileWithDisplayName::class, $result);
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame('', $result->getDisplayName());
        $this->assertNull($result->getAboutMe());
        $this->assertNull($result->getAvatarImageId());
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::getUserProfile
     */
    public function test_pdo_getUserProfile_returns_profile_and_display_name_after_update(): void
    {
        $repo = $this->injector->make(PdoUserProfileRepo::class);
        $userId = $this->getTestUserId();
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Pdo Test Name',
            'about_me' => 'Pdo about me',
        ]));
        $repo->updateProfile($userId, $params);

        $result = $repo->getUserProfile($userId);

        $this->assertSame('Pdo Test Name', $result->getDisplayName());
        $this->assertSame('Pdo about me', $result->getAboutMe());
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::getDisplayNameHistory
     */
    public function test_pdo_getDisplayNameHistory_returns_empty_then_ordered_versions(): void
    {
        $adminUser = $this->createTestAdminUser();
        $userId = $adminUser->getUserId();
        $repo = $this->injector->make(PdoUserProfileRepo::class);

        $historyBefore = $repo->getDisplayNameHistory($userId);
        $this->assertSame([], $historyBefore);

        $repo->updateProfile($userId, UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Name1',
            'about_me' => '',
        ])));
        $repo->updateProfile($userId, UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Name2',
            'about_me' => '',
        ])));

        $history = $repo->getDisplayNameHistory($userId);
        $this->assertCount(2, $history);
        $this->assertSame('Name2', $history[0]->display_name);
        $this->assertSame('Name1', $history[1]->display_name);
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::updateProfile
     */
    public function test_pdo_updateProfile_persists_display_name_and_profile(): void
    {
        $repo = $this->injector->make(PdoUserProfileRepo::class);
        $userId = $this->getTestUserId();
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'Updated Display',
            'about_me' => 'Updated about',
        ]));

        $result = $repo->updateProfile($userId, $params);

        $this->assertSame('Updated Display', $result->getDisplayName());
        $this->assertSame('Updated about', $result->getAboutMe());
        $profile = $repo->getUserProfile($userId);
        $this->assertSame('Updated Display', $profile->getDisplayName());
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo::updateAvatarImage
     */
    public function test_pdo_updateAvatarImage_persists_avatar_id(): void
    {
        $repo = $this->injector->make(PdoUserProfileRepo::class);
        $userId = $this->getTestUserId();
        $repo->updateAvatarImage($userId, 'avatar-id-123');

        $profile = $repo->getUserProfile($userId);
        $this->assertSame('avatar-id-123', $profile->getAvatarImageId());
    }
}
