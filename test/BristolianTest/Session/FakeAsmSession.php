<?php

declare(strict_types=1);

namespace BristolianTest\Session;

use Asm\Session;

/**
 * In-memory implementation of the Asm\Session interface for unit testing.
 * @coversNothing
 */
class FakeAsmSession implements Session
{
    /** @var array<string, int|bool|array<mixed>|string|float> */
    private array $data = [];

    private bool $saved = false;
    private bool $deleted = false;

    /** @var list<array{0:string, 1:string}> */
    private array $headers;

    /**
     * @param list<array{0:string, 1:string}> $headers
     */
    public function __construct(
        private string $sessionId = 'fake-session-id',
        array $headers = []
    ) {
        $this->headers = $headers;
    }

    /**
     * @return list<array{0:string, 1:string}>
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

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @return array<string, int|bool|array<mixed>|string|float>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, int|bool|array<mixed>|string|float> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function save(): void
    {
        $this->saved = true;
    }

    public function close(bool $saveData = true): void
    {
    }

    public function delete(): void
    {
        $this->deleted = true;
        $this->data = [];
    }

    public function __destruct()
    {
    }

    public function isLocked(): bool
    {
        return false;
    }

    public function validateLock(): bool
    {
        return true;
    }

    public function acquireLock(int $lockTimeMS, int $acquireTimeoutMS): void
    {
    }

    public function renewLock(int $milliseconds): void
    {
    }

    public function releaseLock(): void
    {
    }

    public function forceReleaseLocks(): void
    {
    }

    public function isActive(): bool
    {
        return true;
    }

    /**
     * @param int|bool|array<mixed>|string|float $value
     */
    public function set(string $name, int|bool|array|string|float $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * @param int|bool|array<mixed>|string|float|null $default
     * @return int|bool|array<mixed>|string|float|null
     */
    public function get(
        string $name,
        int|bool|array|string|float $default = null,
        bool $clear = false
    ): int|bool|array|string|float|null {
        if (!array_key_exists($name, $this->data)) {
            return $default;
        }

        $value = $this->data[$name];

        if ($clear) {
            unset($this->data[$name]);
        }

        return $value;
    }

    public function wasSaved(): bool
    {
        return $this->saved;
    }

    public function wasDeleted(): bool
    {
        return $this->deleted;
    }
}
