<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTagRepo;

use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\PdoMemeTagRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoMemeTagRepoTest extends MemeTagRepoFixture
{
    private ?string $testUserId = null;
    private ?string $testMemeId = null;

    public function getTestInstance(): MemeTagRepo
    {
        return $this->injector->make(PdoMemeTagRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->testUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId = $adminUser->getUserId();
        }
        return $this->testUserId;
    }

    protected function getTestMemeId(): string
    {
        if ($this->testMemeId === null) {
            $memeStorageRepo = $this->injector->make(\Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class);
            $uploadedFile = \Bristolian\UploadedFiles\UploadedFile::fromFile(__FILE__);
            $this->testMemeId = $memeStorageRepo->storeMeme(
                $this->getTestUserId(),
                'test_meme_' . uniqid() . '.jpg',
                $uploadedFile
            );
            $memeStorageRepo->setUploaded($this->testMemeId);
        }
        return $this->testMemeId;
    }
}
