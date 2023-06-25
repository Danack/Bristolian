<?php

namespace Bristolian\Model;

use Bristolian\ToArray;

class User
{
    use ToArray;

    protected string $user_id;

    /**
     * @param string $user_id
     */
    public function __construct(string $user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * @param $id
     * @param $username
     * @param $password_hash
     */
    public static function new(string $user_id): self
    {
        $instance = new self($user_id);

        return $instance;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->user_id;
    }
}
