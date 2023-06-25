<?php

namespace Bristolian\Model;

use Bristolian\ToArray;

class UserNickname
{
    use ToArray;

    public string $user_id;

    public int $version;

    public string $nickname;

    /**
     * @param $id
     * @param $username
     * @param $password_hash
     */
    public static function new(string $user_id, string $nickname, int $version): self
    {
        $instance = new self();

        $instance->user_id = $user_id;
        $instance->version = $version;
        $instance->nickname = $nickname;

        return $instance;
    }
}
