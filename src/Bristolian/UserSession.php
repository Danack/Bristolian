<?php

namespace Bristolian;

interface UserSession
{
    public function isLoggedIn(): bool;

    public function getUserId(): string;

    public function getUsername(): string;
}
