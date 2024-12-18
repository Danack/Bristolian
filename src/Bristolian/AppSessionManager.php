<?php

namespace Bristolian;

use Asm\Session;
use Asm\SessionManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Asm\RequestSessionStorage;

/**
 * ONLY A SINGLE INSTANCE OF THIS CLASS SHOULD BE CREATED PER REQUEST
 */
class AppSessionManager
{
    private Session|null $session = null;

    public function __construct(
        private Request $request,
        private RequestSessionStorage $sessionStorage,
        private SessionManager $sessionManager
    ) {
    }

    /**
     * @return void
     */
    public function deleteSession()
    {
        $session = $this->sessionStorage->get();

        if ($session !== null) {
            $this->sessionManager->deleteSession($session);
            $this->sessionStorage->markDeleted();
        }
    }

    /**
     * If the user has already started a session, recreate it from
     * the cookie they will have sent the server.
     * @return Session|null
     */
    public function getCurrentSession(): Session|null
    {
        $session = $this->sessionStorage->get();

        // If the session has already been opened,
        // just return it.
        if ($session) {
            return $session;
        }

        // Try to open an already created session from the cookie
        // a user may have sent us.
        // TODO - add optimisation to prevent this happening multiple times?
        $session = $this->sessionManager->openSessionFromCookie($this->request);

        if ($session) {
            $this->sessionStorage->store($session);
        }

        return $session;
    }

    /**
     * Creates a new session. To be used when a user is
     * logging in.
     *
     * @return Session
     */
    public function createSession(): Session
    {
        $session = $this->sessionStorage->get();

        if ($session) {
            // TODO - why would this happen? Maybe throw an error here
            // as it indicates "double-login" ?
            return $session;
        }

        $session = $this->sessionManager->createSession($this->request);
        if ($session) {
            $this->sessionStorage->store($session);
        }
        return $session;
    }

    public function get(): Session|null
    {
        $session = $this->sessionStorage->get();

        return $session;
    }
}
