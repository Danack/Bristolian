<?php

namespace Bristolian\Model;

class User
{
    /**
     * @param string $username
     */
    public function __construct(public readonly string $username)
    {
    }
}
