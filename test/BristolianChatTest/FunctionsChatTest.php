<?php

declare(strict_types = 1);

namespace BristolianChatTest;

use Bristolian\Model\Chat\SystemChatMessage;
use Bristolian\Model\Generated\ChatMessage;
use BristolianChat\ClientHandler\FakeClientHandler;
use BristolianTest\BaseTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

/**
 * @coversNothing
 */
class FunctionsChatTest extends BaseTestCase
{

    /**
     * @covers ::generateFakeChatMessage
     */
    public function test_generateFakeChatMessage_returns_message_with_expected_shape(): void
    {
        $message = generateFakeChatMessage();

        $this->assertInstanceOf(ChatMessage::class, $message);
        $this->assertGreaterThanOrEqual(1000, $message->id);
        $this->assertNotEmpty($message->text);
        $this->assertMatchesRegularExpression('/^user_[a-z]+$/', $message->user_id);
        $this->assertSame('room_12345', $message->room_id);
        $this->assertInstanceOf(\DateTimeInterface::class, $message->created_at);
    }

    /**
     * @covers ::generateFakeChatMessage
     */
    public function test_generateFakeChatMessage_increments_id_on_each_call(): void
    {
        $first = generateFakeChatMessage();
        $second = generateFakeChatMessage();
        $this->assertSame($first->id + 1, $second->id);
    }

    /**
     * @covers ::generateFakeChatMessage
     */
    public function test_generateFakeChatMessage_every_fifth_has_reply_message_id(): void
    {
        $messagesWithReply = null;
        for ($i = 0; $i < 10; $i++) {
            $message = generateFakeChatMessage();
            if ($message->reply_message_id !== null) {
                $messagesWithReply = $message;
                break;
            }
        }

        $this->assertNotNull($messagesWithReply, 'Should get a message with reply_message_id within 10 calls');
        $this->assertLessThan($messagesWithReply->id, $messagesWithReply->reply_message_id, 'reply_message_id must refer to an earlier message');
    }

    /**
     * @return \Generator<string, array{ChatMessage, int, string, int|null}>
     */
    public static function provides_send_user_message_to_clients(): \Generator
    {
        yield 'message without reply' => [
            new ChatMessage(
                42,
                'Hello world',
                'user_123',
                'room_456',
                null,
                new \DateTimeImmutable('2025-01-15 12:00:00')
            ),
            42,
            'Hello world',
            null,
        ];
        yield 'message with reply_message_id' => [
            new ChatMessage(
                99,
                'Reply text',
                'user_abc',
                'room_xyz',
                10,
                new \DateTimeImmutable('2025-02-01 08:30:00')
            ),
            99,
            'Reply text',
            10,
        ];
    }

    /**
     * @covers ::send_user_message_to_clients
     * @covers ::send_data_to_clients
     * @dataProvider provides_send_user_message_to_clients
     */
    public function test_send_user_message_to_clients_broadcasts_to_handler_and_logs(
        ChatMessage $chatMessage,
        int $expectedId,
        string $expectedText,
        int|null $expectedReplyMessageId
    ): void {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $fakeClientHandler = new FakeClientHandler();

        send_user_message_to_clients($chatMessage, $logger, $fakeClientHandler);

        $this->assertTrue(
            $testHandler->hasInfoThatContains('sending message to clients')
        );

        $recordedCalls = $fakeClientHandler->getRecordedCalls();
        $this->assertCount(1, $recordedCalls);
        $this->assertSame([], $recordedCalls[0]['excludedClientIds']);

        $decoded = json_decode($recordedCalls[0]['data'], true);
        $this->assertIsArray($decoded);
        $this->assertSame('message', $decoded['type']);
        $this->assertSame($expectedId, $decoded['chat_message']['id']);
        $this->assertSame($expectedText, $decoded['chat_message']['text']);
        $this->assertSame($expectedReplyMessageId, $decoded['chat_message']['reply_message_id'] ?? null);
    }

    /**
     * @covers ::send_system_message_to_clients
     * @covers ::send_data_to_clients
     */
    public function test_send_system_message_to_clients_broadcasts_to_handler_and_logs(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $fakeClientHandler = new FakeClientHandler();

        $systemMessage = new SystemChatMessage(
            7,
            'room_789',
            'System notification',
            null,
            new \DateTimeImmutable('2025-01-20 14:00:00')
        );

        send_system_message_to_clients($systemMessage, $logger, $fakeClientHandler);

        $this->assertTrue(
            $testHandler->hasInfoThatContains('sending message to clients')
        );

        $recordedCalls = $fakeClientHandler->getRecordedCalls();
        $this->assertCount(1, $recordedCalls);
        $this->assertSame([], $recordedCalls[0]['excludedClientIds']);

        $decoded = json_decode($recordedCalls[0]['data'], true);
        $this->assertIsArray($decoded);
        $this->assertSame('system_message', $decoded['type']);
        $this->assertSame(7, $decoded['system_message']['id']);
        $this->assertSame('room_789', $decoded['system_message']['room_id']);
        $this->assertSame('System notification', $decoded['system_message']['text']);
    }

    /**
     * @covers ::send_system_message_to_clients
     * @covers ::send_data_to_clients
     */
    public function test_send_system_message_to_clients_includes_reply_message_id_when_set(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $fakeClientHandler = new FakeClientHandler();

        $systemMessage = new SystemChatMessage(
            15,
            'room_abc',
            'System reply',
            3,
            new \DateTimeImmutable('2025-02-10 09:00:00')
        );

        send_system_message_to_clients($systemMessage, $logger, $fakeClientHandler);

        $recordedCalls = $fakeClientHandler->getRecordedCalls();
        $this->assertCount(1, $recordedCalls);

        $decoded = json_decode($recordedCalls[0]['data'], true);
        $this->assertSame('system_message', $decoded['type']);
        $this->assertSame(15, $decoded['system_message']['id']);
        $this->assertSame(3, $decoded['system_message']['reply_message_id']);
        $this->assertSame('System reply', $decoded['system_message']['text']);
    }
}
