<?php

declare(strict_types=1);

namespace Bristolian\Session;

use Asm\Driver;
use Asm\Encrypter;
use Asm\Session;
use Asm\SessionManager;
use Asm\Profile\SimpleProfile;
use BristolianTest\Session\FakeAsmSession;

/**
 * In-memory Asm\Driver for unit testing AppSessionManager.
 */
class FakeAsmDriver implements Driver
{
    /** @var array<string, FakeAsmSession> */
    private array $sessions = [];

    private int $nextId = 1;

    /** @var list<array{0:string, 1:string}> */
    private array $defaultHeaders;

    /**
     * @param list<array{0:string, 1:string}> $defaultHeaders
     */
    public function __construct(array $defaultHeaders = [])
    {
        $this->defaultHeaders = $defaultHeaders;
    }

    public function openSessionByID(
        string $sessionID,
        Encrypter $encrypter,
        SessionManager $sessionManager,
        ?SimpleProfile $userProfile
    ): ?Session {
        return $this->sessions[$sessionID] ?? null;
    }

    public function createSession(
        Encrypter $encrypter,
        SessionManager $sessionManager,
        SimpleProfile $userProfile = null
    ): Session {
        $sessionId = 'session-' . $this->nextId++;
        $session = new FakeAsmSession($sessionId, $this->defaultHeaders);
        $this->sessions[$sessionId] = $session;

        return $session;
    }

    public function deleteSessionByID(string $sessionID): void
    {
        unset($this->sessions[$sessionID]);
    }

    public function forceReleaseLockByID(string $sessionID): void
    {
    }

    /**
     * Inject a pre-existing session (for testing openSessionFromCookie paths).
     */
    public function addSession(FakeAsmSession $session): void
    {
        $this->sessions[$session->getSessionId()] = $session;
    }
}
