<?php

declare(strict_types=1);

namespace BristolianTest\Params;

use Bristolian\Model\User;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\NicknameRepo\FakeNicknameRepo;

/**
 * @group wip
 */
class FakeNicknameRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\NicknameRepo\FakeNicknameRepo
     */
    public function testWorks(): void
    {
        $nicknameRepo = new FakeNicknameRepo();
        $user = new User('12345');
        $this->assertNull($nicknameRepo->getUserNickname($user));

        $new_nickname_1 = 'John_1';
        $nicknameRepo->updateUserNickname($user, $new_nickname_1);
        $this->assertSame(
            $new_nickname_1,
            $nicknameRepo->getUserNickname($user)->nickname
        );
        $this->assertSame(
            0,
            $nicknameRepo->getUserNickname($user)->version
        );

        $new_nickname_2 = 'John_2';
        $nicknameRepo->updateUserNickname($user, $new_nickname_2);
        $this->assertSame(
            $new_nickname_2,
            $nicknameRepo->getUserNickname($user)->nickname
        );
        $this->assertSame(
            1,
            $nicknameRepo->getUserNickname($user)->version
        );
    }
}
