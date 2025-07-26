<?php

namespace Asm;

use Asm\Session;


/**
 * Stores the 'global' state of the session.
 */
interface RequestSessionStorage
{
    public function store(Session $session);

    public function get(): Session|null;

    public function markDeleted(): void;
}