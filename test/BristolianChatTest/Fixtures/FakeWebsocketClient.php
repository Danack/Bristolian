<?php

declare(strict_types=1);

namespace BristolianChatTest\Fixtures;

use Amp\Socket\InternetAddress;
use Amp\Socket\SocketAddress;
use Amp\Websocket\WebsocketClient;
use Amp\Websocket\WebsocketCloseCode;
use Amp\Websocket\WebsocketCloseInfo;
use Amp\Websocket\WebsocketCount;
use Amp\Websocket\WebsocketMessage;
use Amp\Websocket\WebsocketTimestamp;

/**
 * Fake WebsocketClient for testing StandardClientHandler::handleClient.
 * Configure with id, remote address string, and messages to return from receive().
 * @coversNothing
 * @implements \IteratorAggregate<int, never>
 */
final class FakeWebsocketClient implements WebsocketClient, \IteratorAggregate
{
    /** @var list<WebsocketMessage|null> */
    private array $receiveQueue = [];

    /** @var \Closure(int, WebsocketCloseInfo):void|null */
    private ?\Closure $onCloseCallback = null;

    private bool $closed = false;

    /**
     * @param array<int, string|WebsocketMessage> $messagesToReceive
     */
    public function __construct(
        private readonly int $id,
        private readonly string $remoteAddressString = '127.0.0.1:12345',
        array $messagesToReceive = [],
    ) {
        foreach ($messagesToReceive as $msg) {
            $this->receiveQueue[] = $msg instanceof WebsocketMessage ? $msg : WebsocketMessage::fromText((string) $msg);
        }
        $this->receiveQueue[] = null;
    }

    public function receive(?\Amp\Cancellation $cancellation = null): ?WebsocketMessage
    {
        $v = array_shift($this->receiveQueue);
        return $v;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLocalAddress(): SocketAddress
    {
        return InternetAddress::fromString('127.0.0.1:0');
    }

    public function getRemoteAddress(): SocketAddress
    {
        return InternetAddress::fromString($this->remoteAddressString);
    }

    public function getTlsInfo(): ?\Amp\Socket\TlsInfo
    {
        return null;
    }

    public function getCloseInfo(): WebsocketCloseInfo
    {
        if (!$this->closed) {
            throw new \Error('Client has not closed');
        }
        return new WebsocketCloseInfo(WebsocketCloseCode::NORMAL_CLOSE, '', 0.0, false);
    }

    public function isCompressionEnabled(): bool
    {
        return false;
    }

    public function sendText(string $data): void
    {
    }

    public function sendBinary(string $data): void
    {
    }

    public function streamText(\Amp\ByteStream\ReadableStream $stream): void
    {
    }

    public function streamBinary(\Amp\ByteStream\ReadableStream $stream): void
    {
    }

    public function ping(): void
    {
    }

    public function getCount(WebsocketCount $type): int
    {
        return 0;
    }

    public function getTimestamp(WebsocketTimestamp $type): float
    {
        return \NAN;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(int $code = WebsocketCloseCode::NORMAL_CLOSE, string $reason = ''): void
    {
        $this->closed = true;
        if ($this->onCloseCallback !== null) {
            ($this->onCloseCallback)($code, new WebsocketCloseInfo($code, $reason, 0.0, false));
        }
    }

    public function onClose(\Closure $onClose): void
    {
        $this->onCloseCallback = $onClose;
    }

    public function getIterator(): \Traversable
    {
        return new \EmptyIterator();
    }
}
