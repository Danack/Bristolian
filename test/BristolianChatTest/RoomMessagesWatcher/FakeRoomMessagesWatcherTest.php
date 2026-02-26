<?php

declare(strict_types=1);

namespace BristolianChatTest\RoomMessagesWatcher;

use BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeRoomMessagesWatcherTest extends BaseTestCase
{
    /**
     * @covers \BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher::__construct
     * @covers \BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher::getInitialPreviousId
     */
    public function test_getInitialPreviousId_returns_zero_when_max_id_null(): void
    {
        $fetcher = new FakeRoomMessagesWatcher(null, []);

        $this->assertSame(0, $fetcher->getInitialPreviousId());
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher::getInitialPreviousId
     */
    public function test_getInitialPreviousId_returns_max_id_when_set(): void
    {
        $fetcher = new FakeRoomMessagesWatcher(42, []);

        $this->assertSame(42, $fetcher->getInitialPreviousId());
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher::getNextChatMessageRowAfter
     */
    public function test_getNextChatMessageRowAfter_returns_rows_in_order_then_null(): void
    {
        $row1 = ['id' => 1, 'room_id' => 'r1', 'text' => 'a', 'reply_message_id' => null, 'user_id' => 'u1', 'created_at' => '2025-01-01T00:00:00+00:00'];
        $row2 = ['id' => 2, 'room_id' => 'r2', 'text' => 'b', 'reply_message_id' => null, 'user_id' => 'u2', 'created_at' => '2025-01-01T00:00:00+00:00'];

        $fetcher = new FakeRoomMessagesWatcher(0, [$row1, $row2]);

        $this->assertSame($row1, $fetcher->getNextChatMessageRowAfter(0));
        $this->assertSame($row2, $fetcher->getNextChatMessageRowAfter(1));
        $this->assertNull($fetcher->getNextChatMessageRowAfter(2));
        $this->assertNull($fetcher->getNextChatMessageRowAfter(2));
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher::getNextChatMessageRowAfter
     */
    public function test_getNextChatMessageRowAfter_returns_null_when_queue_empty(): void
    {
        $fetcher = new FakeRoomMessagesWatcher(10, []);

        $this->assertNull($fetcher->getNextChatMessageRowAfter(10));
    }
}
