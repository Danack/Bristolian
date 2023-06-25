<?php

namespace Bristolian\Repo\NicknameRepo;

use Bristolian\Model\User;
use Bristolian\Model\UserNickname;

class FakeNicknameRepo implements NicknameRepo
{
    /**
     * @var UserNickname[]
     */
    private array $nicknames = [];

    public function getUserNickname(User $user): UserNickname|null
    {
        if (array_key_exists($user->getUserId(), $this->nicknames) !== true) {
            return null;
        }

        $current_nicknames_for_user = $this->nicknames[$user->getUserId()];

        return end($current_nicknames_for_user);
    }

    public function updateUserNickname(User $user, string $newNickname): UserNickname
    {
        $new_nickname = UserNickname::new(
            $user->getUserId(),
            $newNickname,
            0
        );

        if (array_key_exists($user->getUserId(), $this->nicknames) === true) {
            $current_nicknames_for_user = $this->nicknames[$user->getUserId()];
            $current_nickname = end($current_nicknames_for_user);
            /** @var UserNickname $current_nickname */
            $new_nickname = UserNickname::new(
                $user->getUserId(),
                $newNickname,
                $current_nickname->version + 1,
            );
        }

        $this->nicknames[$user->getUserId()][] = $new_nickname;

        return $new_nickname;
    }
}
