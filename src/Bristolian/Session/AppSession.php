<?php

namespace Bristolian\Session;

use Asm\Session as RawSession;
use Bristolian\Model\AdminUser;

class AppSession implements UserSession
{
    const LOGGED_IN = 'LOGGED_IN';
    const USER_ID = 'user_id';
    const USERNAME = 'username';

    public function __construct(private RawSession $raw_session)
    {
    }

    public static function createSessionForUser(
        RawSession $rawSession,
        AdminUser $user
    ): self {

        $instance = new self($rawSession);
        $instance->setUsername($user->getEmailAddress());
        $instance->setUserId($user->getUserId());
        $instance->setLoggedIn(true);

        return $instance;
    }


    public function isLoggedIn(): bool
    {
        return true;//($this->raw_session !== null);
    }

    public function setLoggedIn(bool $logged_in): void
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
        return $this->raw_session->get(self::USER_ID);
    }

    public function getUsername(): string
    {
        return $this->raw_session->get(self::USERNAME);
    }
}
