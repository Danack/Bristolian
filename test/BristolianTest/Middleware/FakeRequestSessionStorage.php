<?php

namespace BristolianTest\Middleware;

use Asm\RequestSessionStorage;
use Asm\Session;

class FakeRequestSessionStorage implements RequestSessionStorage
{
    public function __construct(private Session|null $session)
    {
    }

    public function store(\Asm\Session $session)
    {
        $this->session = $session;
    }

    public function get(): Session|null
    {
        return $this->session;
    }

    public function markDeleted(): void
    {
        // TODO: Implement markDeleted() method.
    }
}
