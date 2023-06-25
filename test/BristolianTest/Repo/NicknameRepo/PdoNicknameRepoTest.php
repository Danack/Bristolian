<?php

declare(strict_types=1);

namespace BristolianTest\Params;

use Bristolian\Model\User;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\NicknameRepo\PdoNicknameRepo;
use Ramsey\Uuid\Uuid;


/**
 * @group wip
 */
class PdoNicknameRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\NicknameRepo\FakeNicknameRepo
     */
    public function testWorks(): void
    {
        $nicknameRepo = $this->injector->make(PdoNicknameRepo::class);
        $user = new User((Uuid::uuid7())->toString());
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

        $new_nickname_3 = 'John_3';
        $nicknameRepo->updateUserNickname($user, $new_nickname_3);
        $this->assertSame(
            $new_nickname_3,
            $nicknameRepo->getUserNickname($user)->nickname
        );
        $this->assertSame(
            2,
            $nicknameRepo->getUserNickname($user)->version
        );
    }
}
