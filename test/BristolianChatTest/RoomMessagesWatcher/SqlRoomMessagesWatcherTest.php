<?php

declare(strict_types=1);

namespace BristolianChatTest\RoomMessagesWatcher;

use BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher;
use BristolianTest\BaseTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * @group db
 */

#[CoversMethod(\BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::class, '__construct')]
#[CoversMethod(\BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::class, 'getInitialPreviousId')]
#[CoversMethod(\BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::class, 'getNextChatMessageAfter')]

class SqlRoomMessagesWatcherTest extends BaseTestCase
{
    public function test_getInitialPreviousId_returns_non_negative_int(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new TestHandler());

        $connection = createMysqlClient();
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $id = $watcher->getInitialPreviousId();
        $this->assertGreaterThanOrEqual(0, $id);
    }

    public function test_getInitialPreviousId_logs_when_no_messages(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $connection = createMysqlClient();
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $id = $watcher->getInitialPreviousId();
        $this->assertGreaterThanOrEqual(0, $id);
        if ($id === 0) {
            $this->assertTrue($testHandler->hasInfoThatContains('There are no previous chat messages.'));
        }
    }

    public function test_getNextChatMessageAfter_returns_null_when_no_later_row(): void
    {
        $logger = new Logger('test');
        $connection = createMysqlClient();
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $message = $watcher->getNextChatMessageAfter(PHP_INT_MAX);
        $this->assertNull($message);
    }
}
