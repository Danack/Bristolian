<?php

namespace Bristolian\Session;

use Asm\Session;
use Bristolian\Exception\BristolianException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bristolian\Session\UserSession;

/**
 *
 * It is used by the middleware, but the request object is only available
 * after the middleware is setup, so we have some unfortunate state in
 * this class.
 */
interface AppSessionManagerInterface
{
    /**
     * @throws BristolianException
     */
    public function initialize(Request $request): void;

    /**
     * @return void
     */
    public function deleteSession(): void;


    /**
     * If the user has already started a session, recreate it from
     * the cookie they will have sent the server.
     * @return UserSession|null
     * @throws BristolianException
     */
    public function getCurrentAppSession(): UserSession|null;

    /**
     * Creates a new session. To be used when a user is
     * logging in.
     *
     * @return Session
     * @throws BristolianException
     */
    public function createRawSession(): Session;

    /**
     * Renews the session and extends the life of the saved data,
     * and returns headers to be sent to the user.
     * @return list<array{0:string, 1:string}>
     */
    public function renewSession(): array;

    /**
     * @return list<array{0:string, 1:string}>
     */
    public function saveIfOpenedAndGetHeaders(): array;
}
