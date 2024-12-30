<?php


use Asm\RequestSessionStorage;
use Asm\Session;

class StandardRequestSessionStorage implements RequestSessionStorage
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

    public function markDeleted(): void
    {
        self::$session = null;
    }
}
