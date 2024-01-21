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
        static $count = 0;

        \error_log("Session created");
        if ($count > 0) {
            \error_log("again");
        }

        $count += 1;
    }

    private function initSession(): void
    {
        // we delay initialising the session so that requests
        // that don't need access to it have lower overhead.
        $this->session = $this->appSessionManager->getCurrentSession();

        // TODO - isn't this an early optimisation?
    }

    public function destroy_session(): void
    {
        // make sure session has been created from cookie
        $this->initSession();
        // delete it.
        $this->appSessionManager->deleteSession();

//        // TODO - wat?
//        $this->initSession();
//        if ($this->session !== null) {
//            $this->session->delete();
//        }
        $this->session = null;
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
