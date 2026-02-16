<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use VarMap\ArrayVarMap;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeUserProfileRepoTest extends UserProfileRepoFixture
{
    public function getTestInstance(): UserProfileRepo
    {
        return new FakeUserProfileRepo();
    }

    /**
     * @covers \Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo::getDisplayNameHistory
     */
    public function test_getDisplayNameHistory_returns_history_after_updateProfile(): void
    {
        $repo = new FakeUserProfileRepo();
        $user_id = $this->getTestUserId();
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'First Name',
            'about_me' => 'About',
        ]));
        $repo->updateProfile($user_id, $params);
        $history = $repo->getDisplayNameHistory($user_id);
        $this->assertCount(1, $history);
        $this->assertSame('First Name', $history[0]->display_name);
    }
}
