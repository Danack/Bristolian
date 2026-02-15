<?php

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use Ramsey\Uuid\Uuid;

/**
 * @group db
 * @coversNothing
 */
class PdoMemeStorageRepoTest extends MemeStorageRepoFixture
{
    use HasTestWorld;
    use TestPlaceholders;

    public function getTestInstance(): MemeStorageRepo
    {
        return $this->injector->make(PdoMemeStorageRepo::class);
    }

    protected function getValidUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo
     */
    public function test_createEntry(): void
    {
        $pdoMemeStorageRepo = $this->make(PdoMemeStorageRepo::class);
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $uuid = Uuid::uuid7();
        $normalized_name = $uuid->toString() . ".jpg";
        $testUser = $this->createTestAdminUser();

        $file_id = $pdoMemeStorageRepo->storeMeme(
            $testUser->getUserId(),
            $normalized_name,
            $uploadedFile
        );

        $pdoMemeStorageRepo->setUploaded($file_id);
    }
}
