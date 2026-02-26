<?php

declare(strict_types=1);

namespace BristolianChatTest\RoomMessagesWatcher;

use BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ThrowingRoomMessagesWatcherTest extends BaseTestCase
{
    /**
     * @covers \BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher::__construct
     * @covers \BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher::getInitialPreviousId
     */
    public function test_getInitialPreviousId_returns_default(): void
    {
        $fetcher = new ThrowingRoomMessagesWatcher();

        $this->assertSame(1, $fetcher->getInitialPreviousId());
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher::getInitialPreviousId
     */
    public function test_getInitialPreviousId_returns_configured_value(): void
    {
        $fetcher = new ThrowingRoomMessagesWatcher(initialPreviousId: 99);

        $this->assertSame(99, $fetcher->getInitialPreviousId());
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher::getNextChatMessageAfter
     */
    public function test_getNextChatMessageAfter_throws_configured_exception(): void
    {
        $exception = new \RuntimeException('Database connection lost');
        $fetcher = new ThrowingRoomMessagesWatcher(exception: $exception);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection lost');

        $fetcher->getNextChatMessageAfter(1);
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher::getNextChatMessageAfter
     */
    public function test_getNextChatMessageAfter_throws_custom_exception(): void
    {
        $exception = new \InvalidArgumentException('Invalid previous_id');
        $fetcher = new ThrowingRoomMessagesWatcher(exception: $exception);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid previous_id');

        $fetcher->getNextChatMessageAfter(0);
    }
}
