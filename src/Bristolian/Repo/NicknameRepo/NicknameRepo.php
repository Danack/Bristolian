<?php

namespace Bristolian\Repo\NicknameRepo;

use Bristolian\Model\User;
use Bristolian\Model\UserNickname;

interface NicknameRepo
{
    public function getUserNickname(User $user): UserNickname|null;

    public function updateUserNickname(User $user, string $newNickname): UserNickname;
}
