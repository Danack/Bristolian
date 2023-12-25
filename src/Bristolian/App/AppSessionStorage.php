<?php

namespace Bristolian\App;

use Asm\Session;
use Asm\RequestSessionStorage;

class AppSessionStorage implements RequestSessionStorage
{
    private static Session|null $session = null;

    public function get(): Session|null
    {
        return self::$session;
    }

    public function store(\Asm\Session $session): void
    {
        self::$session = $session;
    }
}
