<?php

namespace Bristolian\Session;

use Bristolian\Session\UserSession;

class MockUserSession implements UserSession
{
    public function __construct(
        private bool $isLoggedIn,
        private string $userId,
        private string $username
    ) {
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
