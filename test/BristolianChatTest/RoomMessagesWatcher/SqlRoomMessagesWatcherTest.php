<?php

declare(strict_types=1);

namespace BristolianChatTest\RoomMessagesWatcher;

use BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher;
use BristolianTest\BaseTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

/**
 * @coversNothing
 * @group db
 */
class SqlRoomMessagesWatcherTest extends BaseTestCase
{
    /**
     * @covers \BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::__construct
     * @covers \BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::getInitialPreviousId
     * @covers \BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::getNextChatMessageRowAfter
     */
    public function test_getInitialPreviousId_returns_non_negative_int(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new TestHandler());

        $connection = createMysqlClient();
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $id = $watcher->getInitialPreviousId();
        $this->assertIsInt($id);
        $this->assertGreaterThanOrEqual(0, $id);
    }

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::getInitialPreviousId
     */
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

    /**
     * @covers \BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::getNextChatMessageRowAfter
     */
    public function test_getNextChatMessageRowAfter_returns_null_when_no_later_row(): void
    {
        $logger = new Logger('test');
        $connection = createMysqlClient();
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $row = $watcher->getNextChatMessageRowAfter(PHP_INT_MAX);
        $this->assertNull($row);
    }
}
