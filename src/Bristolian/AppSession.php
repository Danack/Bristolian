<?php

namespace Bristolian;

use Asm\Session as RawSession;
use Bristolian\Model\AdminUser;

class AppSession implements UserSession
{
    const LOGGED_IN = 'LOGGED_IN';
    const USER_ID = 'user_id';
    const USERNAME = 'username';

//    private RawSession|null $raw_session = null;

//    public function __construct(private AppSessionManager $appSessionManager)
//    {
//        static $count = 0;
//
//        if ($count > 0) {
//            \error_log("Session created again");
//        }
//
//        $count += 1;
//    }

    public function __construct(private RawSession $raw_session)
    {
    }


//    private function initSession(): void
//    {
//        // we delay initialising the session so that requests
//        // that don't need access to it have lower overhead.
//        $this->raw_session = $this->appSessionManager->getCurrentSession();
//
//        // TODO - isn't this an early optimisation?
//    }

//    public function destroy_session(): void
//    {
//        // make sure session has been created from cookie
//        $this->initSession();
//        // delete it.
//        $this->appSessionManager->deleteSession();
//
////        // TODO - wat?
////        $this->initSession();
////        if ($this->session !== null) {
////            $this->session->delete();
////        }
//        $this->raw_session = null;
//    }

//    public function createSessionForUser(AdminUser $user): void
//    {
//        $this->raw_session = $this->appSessionManager->createRawSession();
//        $this->setUsername($user->getEmailAddress());
//        $this->setUserId($user->getUserId());
//    }

    public static function createSessionForUser(
//        AppSessionManager $appSessionManager,
        RawSession $rawSession,
        AdminUser $user
    ) {
//        $rawSession = $appSessionManager->createRawSession();
        $instance = new self($rawSession);
        $instance->setUsername($user->getEmailAddress());
        $instance->setUserId($user->getUserId());
        $instance->setLoggedIn(true);

        return $instance;
    }


    public function isLoggedIn(): bool
    {
//        $this->initSession();
        return ($this->raw_session !== null);
    }

    public function setLoggedIn(bool $logged_in)
    {
        $this->raw_session->set(self::LOGGED_IN, $logged_in);
    }

    public function setUserId(string $user_id): void
    {
        $this->raw_session->set(self::USER_ID, $user_id);
    }

    public function setUsername(string $username): void
    {
        $this->raw_session->set(self::USERNAME, $username);
    }


    public function getUserId(): string
    {
//        $this->initSession();

        return $this->raw_session->get(self::USER_ID);
    }

    public function getUsername(): string
    {
        return $this->raw_session->get(self::USERNAME);
    }
}
