<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo;
use BristolianTest\Repo\DbTransactionIsolation;
use BristolianTest\Support\HasTestWorld;

/**
 * @group db
 * @coversNothing
 */
class PdoChatMessageRepoTest extends ChatMessageRepoFixture
{
//    use DbTransactionIsolation;
    use HasTestWorld;

//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->dbTransactionSetUp();
//    }
//
//    public function tearDown(): void
//    {
//        $this->dbTransactionTearDown();
//        parent::tearDown();
//    }

    protected function dbTransactionClearTables(): void
    {
        $pdoSimple = $this->injector->make(PdoSimple::class);
//        $pdoSimple->execute('DELETE FROM chat_message', []);
    }

    public function getTestInstance(): ChatMessageRepo
    {
        return $this->injector->make(PdoChatMessageRepo::class);
    }

    protected function getTestUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }

    protected function getTestRoomId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getHousingRoom()->id;
    }

    protected function getOtherRoomId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getOffTopicRoom()->id;
    }
}
