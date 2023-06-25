<?php

namespace Bristolian;

use Asm\Session;
use Asm\SessionManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Asm\RequestSessionStorage;

class AppSessionManager
{
    private Session|null $session = null;

    public function __construct(
        private Request $request,
        private RequestSessionStorage $sessionStorage,
        private SessionManager $sessionManager
    ) {
    }

    public function getCurrentSession(): Session|null
    {
        if ($this->session) {
            return $this->session;
        }

        // TODO - add optimisation to prevent this happening multiple times?
        $this->session = $this->sessionManager->openSessionFromCookie($this->request);

        if ($this->session) {
            $this->sessionStorage->store($this->session);
        }

        return $this->session;
    }

    public function createSession(): Session
    {
        if ($this->session) {
            return $this->session;
        }

        $this->session = $this->sessionManager->createSession($this->request);
        $this->sessionStorage->store($this->session);
        return $this->session;
    }

    public function get(): Session|null
    {
        error_log('getting session');
        return $this->session;
    }
}
