<?php

declare(strict_types=1);

namespace BristolianChatTest\RoomMessagesWatcher;

use BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher;
use BristolianTest\BaseTestCase;
use BristolianTest\Support\HasTestWorld;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Bristolian\Parameters\ChatMessageParam;
use Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo;
use PHPUnit\Framework\Attributes\CoversClass;
use VarMap\ArrayVarMap;

/**
 * @group db
 */

#[CoversClass(\BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher::class)]

class SqlRoomMessagesWatcherTest extends BaseTestCase
{
    use HasTestWorld;

    public function test_getInitialPreviousId_returns_non_negative_int(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new TestHandler());

        $connection = $this->injector->make(\Amp\Mysql\MysqlConnection::class);
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $id = $watcher->getInitialPreviousId();
        $this->assertGreaterThanOrEqual(0, $id);
    }

    public function test_getInitialPreviousId_logs_when_no_messages(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $connection = $this->injector->make(\Amp\Mysql\MysqlConnection::class);
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
        $connection = $this->injector->make(\Amp\Mysql\MysqlConnection::class);
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);

        $message = $watcher->getNextChatMessageAfter(PHP_INT_MAX);
        $this->assertNull($message);
    }

    public function test_getNextChatMessageAfter_returns_stored_message(): void
    {
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $roomId = $this->standardTestData()->getHousingRoom()->id;

        $logger = new Logger('test');
        $connection = $this->injector->make(\Amp\Mysql\MysqlConnection::class);
        $watcher = new SqlRoomMessagesWatcher($connection, $logger);
        $previousIdBeforeInsert = $watcher->getInitialPreviousId();

        $chatMessageRepo = $this->injector->make(PdoChatMessageRepo::class);
        $uniqueText = 'Sql watcher coverage message ' . create_test_uniqid();
        $param = ChatMessageParam::createFromVarMap(new ArrayVarMap([
            'room_id' => $roomId,
            'text' => $uniqueText,
        ]));
        $chatMessageRepo->storeChatMessageForUser($userId, $param);

        $message = $watcher->getNextChatMessageAfter($previousIdBeforeInsert);
        $this->assertNotNull($message);
        $this->assertSame($uniqueText, $message->text);
        $this->assertSame($roomId, $message->room_id);
    }
}
