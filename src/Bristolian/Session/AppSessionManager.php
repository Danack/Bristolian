<?php

namespace Bristolian\Session;

use Asm\RequestSessionStorage;
use Asm\Session;
use Asm\SessionManager;
use Bristolian\BristolianException;
use Bristolian\Model\AdminUser;
use Bristolian\Session\AppSession;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * ONLY A SINGLE INSTANCE OF THIS CLASS SHOULD BE CREATED PER REQUEST
 *
 * It is used by the middleware, but the request object is only available
 * after the middleware is setup, so we have some unfortunate state in
 * this class.
 */
class AppSessionManager
{
    /**
     * The underlying 'raw' session that is not aware of the application
     * @var Session|null
     */
    private Session|null $session = null;

    private Request|null $request = null;

    public function __construct(private SessionManager $sessionManager)
    {
    }

    private function checkInitialised(): void
    {
        if ($this->request === null) {
            throw new BristolianException("AppSessionManager is not initialized.");
        }
    }

    /**
     * @throws BristolianException
     */
    public function initialize(Request $request): void
    {
        if ($this->request !== null) {
            throw new BristolianException("AppSessionManager is already initialized.");
        }

        $this->request = $request;
    }

    /**
     * @return void
     */
    public function deleteSession(): void
    {
        $this->checkInitialised();
        // Make sure session has been read from cookie if not
        // otherwise initialised.
        $this->getCurrentAppSession();

        if ($this->session) {
            $this->session->delete();
        }
        $this->session = null;
    }

    public function getRawSession(): Session|null
    {
        $this->checkInitialised();

        return $this->session;
    }

    /**
     * If the user has already started a session, recreate it from
     * the cookie they will have sent the server.
     * @return AppSession|null
     * @throws BristolianException
     */
    public function getCurrentAppSession(): AppSession|null
    {
        $this->checkInitialised();

        // If the session has already been opened,
        // just return it.
        if ($this->session) {
            return new AppSession($this->session);
        }

        // Try to open an already created session from the cookie
        // a user may have sent us.
        $this->session = $this->sessionManager->openSessionFromCookie($this->request);

        if ($this->session) {
            return new AppSession($this->session);
        }

        return null;
    }

    /**
     * Creates a new session. To be used when a user is
     * logging in.
     *
     * @return Session
     * @throws BristolianException
     */
    public function createRawSession(): Session
    {
        $this->checkInitialised();

        if ($this->session) {
            // TODO - why would this happen? Maybe throw an error here
            // as it indicates "double-login" ?
            return $this->session;
        }

        $this->session = $this->sessionManager->createSession($this->request);
        return $this->session;
    }

    public function get(): AppSession|null
    {
        $this->checkInitialised();

        if ($this->session) {
            return new AppSession($this->session);
        }

        return null;
    }
}
