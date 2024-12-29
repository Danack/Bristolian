<?php

namespace BristolianTest\Middleware;

use Asm\AsmException;
use \Asm\Session;

class FakeSession implements Session
{
    /**
     * @param array{0:string, 1:string} $headers
     */
    public function __construct(private array $headers)
    {
    }


    /**
     * @param string $privacy
     * @param string|null $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return array{0:string, 1:string}
     */
    public function getHeaders(
        string $privacy,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true
    ): array {
        return $this->headers;
    }

    public function getSessionId()
    {
        // TODO: Implement getSessionId() method.
    }

    public function getData()
    {
        // TODO: Implement getData() method.
    }

    public function setData(array $data)
    {
        // TODO: Implement setData() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function close(bool $saveData = true)
    {
        // TODO: Implement close() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function isLocked(): bool
    {
        // TODO: Implement isLocked() method.
    }

    public function validateLock(): bool
    {
        // TODO: Implement validateLock() method.
    }

    public function acquireLock(int $lockTimeMS, int $acquireTimeoutMS)
    {
        // TODO: Implement acquireLock() method.
    }

    public function renewLock(int $milliseconds)
    {
        // TODO: Implement renewLock() method.
    }

    public function releaseLock()
    {
        // TODO: Implement releaseLock() method.
    }

    public function forceReleaseLocks()
    {
        // TODO: Implement forceReleaseLocks() method.
    }

    public function isActive()
    {
        // TODO: Implement isActive() method.
    }

    public function set(string $name, float|int|bool|array|string $value): void
    {
        // TODO: Implement set() method.
    }

    public function get(string $name, float|int|bool|array|string $default = null, bool $clear = false): int|bool|array|string|float|null
    {
        // TODO: Implement get() method.
    }
}
