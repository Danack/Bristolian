<?php

namespace Bristolian;

use Asm\RequestSessionStorage;
use Asm\Session;
use Psr\Http\Message\ServerRequestInterface as Request;

class AppSession
{
    const USERNAME = 'username';

    private Session|null $session = null;

    public function __construct(private AppSessionManager $appSessionManager)
    {
    }

    private function initSession(): void
    {
        $this->session = $this->appSessionManager->getCurrentSession();
    }

    private function createSession(): void
    {
        $this->session = $this->appSessionManager->createSession();
    }

    public function createSessionForUser(string $username/*$userProfile*/): void
    {
        $this->session = $this->appSessionManager->createSession();
        $this->setUsername($username);
    }

    public function isLoggedIn(): bool
    {
        $this->initSession();
        return ($this->session !== null);
    }

    public function setUsername(string $username): void
    {
        $this->session->set(self::USERNAME, $username);
    }

    public function getUsername()
    {
        return $this->session->get(self::USERNAME);
    }
}