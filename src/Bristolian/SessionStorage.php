<?php

namespace Bristolian;

use Asm\RequestSessionStorage;
use Asm\Session;



class SessionStorage implements RequestSessionStorage
{
    private static Session|null $session = null;

    public function store(Session $session): void
    {
        self::$session = $session;
    }

    public function get(): Session|null
    {
        return self::$session;
    }
}