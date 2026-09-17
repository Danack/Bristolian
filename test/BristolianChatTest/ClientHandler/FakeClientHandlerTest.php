<?php

declare(strict_types=1);

namespace BristolianChatTest\ClientHandler;

use BristolianChat\ClientHandler\FakeClientHandler;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\BristolianChat\ClientHandler\FakeClientHandler::class, 'broadcastText')]
#[CoversMethod(\BristolianChat\ClientHandler\FakeClientHandler::class, 'getRecordedCalls')]

class FakeClientHandlerTest extends BaseTestCase
{
    public function test_broadcastText_records_call_and_getRecordedCalls_returns_it(): void
    {
        $handler = new FakeClientHandler();

        $handler->broadcastText('{"type":"message"}', []);

        $calls = $handler->getRecordedCalls();
        $this->assertCount(1, $calls);
        $this->assertSame('{"type":"message"}', $calls[0]['data']);
        $this->assertSame([], $calls[0]['excludedClientIds']);
    }

    public function test_broadcastText_with_excludedClientIds_records_them(): void
    {
        $handler = new FakeClientHandler();

        $handler->broadcastText('data', ['id1', 'id2']);

        $calls = $handler->getRecordedCalls();
        $this->assertCount(1, $calls);
        $this->assertSame(['id1', 'id2'], $calls[0]['excludedClientIds']);
    }

    public function test_multiple_broadcastText_calls_recorded_in_order(): void
    {
        $handler = new FakeClientHandler();

        $handler->broadcastText('first', []);
        $handler->broadcastText('second', ['excluded']);

        $calls = $handler->getRecordedCalls();
        $this->assertCount(2, $calls);
        $this->assertSame('first', $calls[0]['data']);
        $this->assertSame('second', $calls[1]['data']);
        $this->assertSame(['excluded'], $calls[1]['excludedClientIds']);
    }
}
