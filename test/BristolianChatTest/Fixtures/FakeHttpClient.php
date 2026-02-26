<?php

declare(strict_types=1);

namespace BristolianChatTest\Fixtures;

use Amp\Http\Server\Driver\Client;
use Amp\Socket\InternetAddress;
use Amp\Socket\SocketAddress;
use Amp\Socket\TlsInfo;

/**
 * Fake Driver\Client for building Amp\Http\Server\Request in tests.
 */
final class FakeHttpClient implements Client
{
    /** @var \Closure():void|null */
    private ?\Closure $onCloseCallback = null;

    private bool $closed = false;

    public function __construct(
        private readonly int $id,
        private readonly string $remoteAddressString = '127.0.0.1:12345',
        private readonly string $localAddressString = '0.0.0.0:80',
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRemoteAddress(): SocketAddress
    {
        return InternetAddress::fromString($this->remoteAddressString);
    }

    public function getLocalAddress(): SocketAddress
    {
        return InternetAddress::fromString($this->localAddressString);
    }

    public function getTlsInfo(): ?TlsInfo
    {
        return null;
    }

    public function close(): void
    {
        $this->closed = true;
        if ($this->onCloseCallback !== null) {
            ($this->onCloseCallback)();
        }
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function onClose(\Closure $onClose): void
    {
        $this->onCloseCallback = $onClose;
    }
}
