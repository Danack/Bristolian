<?php

namespace Bristolian;

use Asm\RequestSessionStorage;
use Asm\Session;
use Bristolian\Model\AdminUser;
use Psr\Http\Message\ServerRequestInterface as Request;

class AppSession implements UserSession
{
    const USER_ID = 'user_id';
    const USERNAME = 'username';

    private Session|null $session = null;

    public function __construct(private AppSessionManager $appSessionManager)
    {
    }

    private function initSession(): void
    {
        $this->session = $this->appSessionManager->getCurrentSession();
    }

    public function createSessionForUser(AdminUser $user): void
    {
        $this->session = $this->appSessionManager->createSession();
        $this->setUsername($user->getEmailAddress());
        $this->setUserId($user->getUserId());
    }

    public function isLoggedIn(): bool
    {
        $this->initSession();
        return ($this->session !== null);
    }

    public function setUserId(string $user_id): void
    {
        $this->session->set(self::USER_ID, $user_id);
    }

    public function setUsername(string $username): void
    {
        $this->session->set(self::USERNAME, $username);
    }


    public function getUserId(): string
    {
        return $this->session->get(self::USER_ID);
    }

    public function getUsername(): string
    {
        return $this->session->get(self::USERNAME);
    }
}
