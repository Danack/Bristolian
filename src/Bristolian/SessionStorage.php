<?php

namespace Bristolian;

use Asm\RequestSessionStorage;
use Asm\Session;

class SessionStorage implements RequestSessionStorage
{
    private Session|null $session = null;

    public function store(Session $session): void
    {
        $this->session = $session;
    }

    public function get(): Session|null
    {
        return $this->session;
    }
}
