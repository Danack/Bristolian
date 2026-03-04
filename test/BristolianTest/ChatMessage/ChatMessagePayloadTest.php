<?php

declare(strict_types=1);

namespace BristolianTest\ChatMessage;

use Bristolian\ChatMessage\ChatMessagePayload;
use Bristolian\ChatMessage\ChatType;
use Bristolian\Model\Chat\SystemChatMessage;
use Bristolian\Model\Generated\ChatMessage;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ChatMessagePayloadTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\ChatMessage\ChatMessagePayload
     * @covers \Bristolian\ChatMessage\ChatMessagePayload::__construct
     * @covers \Bristolian\ChatMessage\ChatType
     */
    public function test_create_from_user_message_returns_payload_with_chat_message(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $chatMessage = new ChatMessage(
            id: 1,
            text: 'Hello',
            user_id: 'user-1',
            room_id: 'room-1',
            reply_message_id: null,
            created_at: $createdAt
        );

        $payload = ChatMessagePayload::create_from_user_message($chatMessage);

        $this->assertSame(ChatType::USER_MESSAGE, $payload->type);
        $array = $payload->toArray();
        $this->assertSame('message', $array['type']);
        $this->assertArrayHasKey('chat_message', $array);
        $this->assertSame($chatMessage, $array['chat_message']);
        $this->assertArrayNotHasKey('system_message', $array);
    }

    /**
     * @covers \Bristolian\ChatMessage\ChatMessagePayload
     * @covers \Bristolian\ChatMessage\ChatType
     */
    public function test_create_from_system_message_returns_payload_with_system_message(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $systemMessage = new SystemChatMessage(
            id: 2,
            room_id: 'room-2',
            text: 'System notice',
            reply_message_id: null,
            created_at: $createdAt
        );

        $payload = ChatMessagePayload::create_from_system_message($systemMessage);

        $this->assertSame(ChatType::SYSTEM_MESSAGE, $payload->type);
        $array = $payload->toArray();
        $this->assertSame('system_message', $array['type']);
        $this->assertArrayHasKey('system_message', $array);
        $this->assertSame($systemMessage, $array['system_message']);
        $this->assertArrayNotHasKey('chat_message', $array);
    }
}
