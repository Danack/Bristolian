<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeMemeStorageRepoTest extends MemeStorageRepoFixture
{
    /**
     * @return MemeStorageRepo
     */
    public function getTestInstance(): MemeStorageRepo
    {
        return new FakeMemeStorageRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user_123';
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::setUploaded
     */
    public function test_setUploaded_updates_state(): void
    {
        $repo = new FakeMemeStorageRepo();
        $meme_id = $repo->storeMeme('user_1', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo->setUploaded($meme_id);
        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertSame(\Bristolian\Repo\MemeStorageRepo\MemeFileState::UPLOADED->value, $meme->state);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::markAsDeleted
     */
    public function test_markAsDeleted_updates_meme(): void
    {
        $repo = new FakeMemeStorageRepo();
        $meme_id = $repo->storeMeme('user_1', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo->markAsDeleted($meme_id);
        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertTrue($meme->deleted);
    }
}
