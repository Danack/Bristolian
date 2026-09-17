<?php

declare(strict_types=1);

namespace BristolianChatTest;

use Bristolian\MarkdownRenderer\FakeMarkdownRenderer;
use BristolianChat\ClientHandler\FakeClientHandler;
use BristolianChat\RoomMessageFetcher;
use BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher;
use BristolianChat\RoomMessagesWatcher\ThrowingRoomMessagesWatcher;
use BristolianTest\BaseTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\BristolianChat\RoomMessageFetcher::class, '__construct')]
#[CoversMethod(\BristolianChat\RoomMessageFetcher::class, 'getMessage')]
#[CoversMethod(\BristolianChat\RoomMessageFetcher::class, 'initialize_previous_id')]
#[CoversMethod(\BristolianChat\RoomMessageFetcher::class, 'runOneIteration')]
#[CoversMethod(\BristolianChat\RoomMessagesWatcher\FakeRoomMessagesWatcher::class, 'getInitialPreviousId')]

class RoomMessageFetcherTest extends BaseTestCase
{
    public function test_runOneIteration_initializes_previous_id_to_zero_when_no_messages(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $fetcher = new FakeRoomMessagesWatcher(null, []);
        $clientHandler = new FakeClientHandler();

        $markdownRenderer = new FakeMarkdownRenderer();

        $watcher = new RoomMessageFetcher(
            $markdownRenderer,
            $fetcher,
            $clientHandler,
            $logger
        );

        $watcher->runOneIteration();

        $this->assertCount(0, $clientHandler->getRecordedCalls());
    }

    public function test_runOneIteration_when_no_next_message_does_not_send(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $fetcher = new FakeRoomMessagesWatcher(100, []);
        $clientHandler = new FakeClientHandler();

        $markdownRenderer = new FakeMarkdownRenderer();

        $watcher = new RoomMessageFetcher(
            $markdownRenderer,
            $fetcher,
            $clientHandler,
            $logger
        );

        $watcher->runOneIteration();

        $this->assertCount(0, $clientHandler->getRecordedCalls());
    }

    public function test_runOneIteration_fetches_message_and_sends_to_clients(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $row = [
            'id' => 5,
            'reply_message_id' => null,
            'room_id' => 'room_1',
            'user_id' => 'user_1',
            'text' => 'Hello from test',
            'created_at' => '2025-01-15T12:00:00+00:00',
        ];

        $fetcher = new FakeRoomMessagesWatcher(4, [$row]);
        $clientHandler = new FakeClientHandler();

        $markdownRenderer = new FakeMarkdownRenderer();

        $watcher = new RoomMessageFetcher($markdownRenderer, $fetcher, $clientHandler, $logger);

        $watcher->runOneIteration();

        $this->assertTrue($testHandler->hasInfoThatContains('Pulled chat message from MySQL'));
        $this->assertTrue($testHandler->hasInfoThatContains('sending message to clients'));

        $recordedCalls = $clientHandler->getRecordedCalls();
        $this->assertCount(1, $recordedCalls);
        $decoded = json_decode($recordedCalls[0]['data'], true);
        $this->assertSame('message', $decoded['type']);
        $this->assertSame(5, $decoded['chat_message']['id']);
        $this->assertSame('Hello from test', $decoded['chat_message']['text']);
    }

    public function test_runOneIteration_logs_error_when_exception_thrown(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $fetcher = new ThrowingRoomMessagesWatcher(
            initialPreviousId: 1,
            exception: new \RuntimeException('Database connection lost')
        );
        $clientHandler = new FakeClientHandler();

        $markdownRenderer = new FakeMarkdownRenderer();
        $watcher = new RoomMessageFetcher($markdownRenderer, $fetcher, $clientHandler, $logger);

        $watcher->runOneIteration();

        $this->assertTrue($testHandler->hasErrorThatContains('Exception watching for new messages: Database connection lost'));
        $this->assertCount(0, $clientHandler->getRecordedCalls());
    }
}
