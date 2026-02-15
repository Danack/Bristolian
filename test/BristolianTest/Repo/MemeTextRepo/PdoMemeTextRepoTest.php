<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTextRepo;

use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo;
use BristolianTest\Repo\DbTransactionIsolation;

/**
 * @group db
 * @coversNothing
 */
class PdoMemeTextRepoTest extends MemeTextRepoFixture
{
//    use DbTransactionIsolation;

    private ?string $testUserId = null;
    private ?string $testUserId2 = null;

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
//        $pdoSimple->execute('DELETE FROM meme_tag', []);
//        $pdoSimple->execute('DELETE FROM meme_text', []);
//        $pdoSimple->execute('DELETE FROM stored_meme', []);
    }

    public function getTestInstance(): MemeTextRepo
    {
        return $this->injector->make(PdoMemeTextRepo::class);
    }

    protected function getMemeStorageRepo(): MemeStorageRepo
    {
        return $this->injector->make(\Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->testUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId = $adminUser->getUserId();
        }
        return $this->testUserId;
    }

    protected function getTestUserId2(): string
    {
        if ($this->testUserId2 === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId2 = $adminUser->getUserId();
        }
        return $this->testUserId2;
    }
}
