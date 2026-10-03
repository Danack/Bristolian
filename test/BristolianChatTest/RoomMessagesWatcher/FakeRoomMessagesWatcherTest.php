<?php

declare(strict_types=1);

namespace BristolianChatTest\RoomMessagesWatcher;

use Bristolian\Model\Chat\UserChatMessage;
use BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Safe\DateTimeImmutable;

#[CoversClass(FakeRoomMessagesWatcher::class)]
class FakeRoomMessagesWatcherTest extends BaseTestCase
{
    public function test_getInitialPreviousId_returns_zero_when_max_id_null(): void
    {
        $fetcher = new FakeRoomMessagesWatcher(null, []);

        $this->assertSame(0, $fetcher->getInitialPreviousId());
    }

    public function test_getInitialPreviousId_returns_max_id_when_set(): void
    {
        $fetcher = new FakeRoomMessagesWatcher(42, []);

        $this->assertSame(42, $fetcher->getInitialPreviousId());
    }

    public function test_getNextChatMessageAfter_returns_messages_in_order_then_null(): void
    {
        $row1 = ['id' => 1, 'room_id' => 'r1', 'text' => 'a', 'reply_message_id' => null, 'user_id' => 'u1', 'created_at' => '2025-01-01T00:00:00+00:00'];
        $row2 = ['id' => 2, 'room_id' => 'r2', 'text' => 'b', 'reply_message_id' => null, 'user_id' => 'u2', 'created_at' => '2025-01-01T00:00:00+00:00'];

        $fetcher = new FakeRoomMessagesWatcher(0, [$row1, $row2]);

        $msg1 = $fetcher->getNextChatMessageAfter(0);
        $this->assertInstanceOf(UserChatMessage::class, $msg1);
        $this->assertSame(1, $msg1->id);
        $this->assertSame('a', $msg1->text);

        $msg2 = $fetcher->getNextChatMessageAfter(1);
        $this->assertInstanceOf(UserChatMessage::class, $msg2);
        $this->assertSame(2, $msg2->id);
        $this->assertSame('b', $msg2->text);

        $this->assertNull($fetcher->getNextChatMessageAfter(2));
        $this->assertNull($fetcher->getNextChatMessageAfter(2));
    }

    public function test_getNextChatMessageAfter_returns_null_when_queue_empty(): void
    {
        $fetcher = new FakeRoomMessagesWatcher(10, []);

        $this->assertNull($fetcher->getNextChatMessageAfter(10));
    }

    public function test_getNextChatMessageAfter_parses_datetime_object_and_reply_id(): void
    {
        $createdAt = new DateTimeImmutable('2025-03-01T10:00:00+00:00');
        $row = [
            'id' => 9,
            'room_id' => 'room-x',
            'text' => 'reply test',
            'reply_message_id' => 3,
            'user_id' => 'user-x',
            'created_at' => $createdAt,
        ];
        $fetcher = new FakeRoomMessagesWatcher(8, [$row]);

        $message = $fetcher->getNextChatMessageAfter(8);
        $this->assertNotNull($message);
        $this->assertSame(9, $message->id);
        $this->assertSame(3, $message->reply_message_id);
        $this->assertSame($createdAt, $message->created_at);
    }
}
