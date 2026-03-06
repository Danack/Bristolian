<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Exception\BristolianException;
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

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::listAllMemes
     */
    public function test_listAllMemes_returns_non_deleted_memes(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'a.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_1', 'b.jpg', UploadedFile::fromFile(__FILE__));

        $all = $repo->listAllMemes();
        $this->assertCount(2, $all);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::listMemesForUserWithNoTags
     */
    public function test_listMemesForUserWithNoTags_delegates_to_listMemesForUser(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'x.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->listMemesForUserWithNoTags('user_1');
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::searchMemesForUser
     */
    public function test_searchMemesForUser_returns_memes_for_user(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'f.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->searchMemesForUser('user_1', null, null);
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::searchMemesByExactTags
     */
    public function test_searchMemesByExactTags_empty_returns_listMemesForUser(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'f.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->searchMemesByExactTags('user_1', []);
        $this->assertCount(1, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::searchMemesByExactTags
     */
    public function test_searchMemesByExactTags_with_tags_returns_empty(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'f.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->searchMemesByExactTags('user_1', ['tag1']);
        $this->assertCount(0, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::getStoredMeme
     */
    public function test_getStoredMeme_returns_all_stored(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'one.jpg', UploadedFile::fromFile(__FILE__));

        $stored = $repo->getStoredMeme();
        $this->assertCount(1, $stored);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::setUploaded
     */
    public function test_setUploaded_throws_when_meme_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('meme not found to set as uploaded.');

        $repo->setUploaded('nonexistent-id');
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::markAsDeleted
     */
    public function test_markAsDeleted_throws_when_meme_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('meme not found to mark as deleted.');

        $repo->markAsDeleted('nonexistent-id');
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::getMeme
     */
    public function test_getMeme_returns_stored_meme(): void
    {
        $repo = new FakeMemeStorageRepo();
        $meme_id = $repo->storeMeme('user_1', 'g.jpg', UploadedFile::fromFile(__FILE__));

        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertSame($meme_id, $meme->id);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::getByNormalizedName
     */
    public function test_getByNormalizedName_returns_meme_when_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'normalized.jpg', UploadedFile::fromFile(__FILE__));

        $meme = $repo->getByNormalizedName('normalized.jpg');
        $this->assertNotNull($meme);
        $this->assertSame('normalized.jpg', $meme->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::getByNormalizedName
     */
    public function test_getByNormalizedName_returns_null_when_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'other.jpg', UploadedFile::fromFile(__FILE__));

        $this->assertNull($repo->getByNormalizedName('nonexistent.jpg'));
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::storeMeme
     */
    public function test_storeMeme_creates_meme_and_returns_id(): void
    {
        $repo = new FakeMemeStorageRepo();
        $id = $repo->storeMeme('user_1', 'stored.jpg', UploadedFile::fromFile(__FILE__));

        $this->assertNotEmpty($id);
        $meme = $repo->getMeme($id);
        $this->assertSame('user_1', $meme->user_id);
        $this->assertSame('stored.jpg', $meme->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::listMemesForUser
     */
    public function test_listMemesForUser_returns_only_that_users_memes(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'a.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_1', 'b.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_2', 'c.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->listMemesForUser('user_1');
        $this->assertCount(2, $memes);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::listAllMemes
     */
    public function test_listAllMemes_excludes_deleted(): void
    {
        $repo = new FakeMemeStorageRepo();
        $id1 = $repo->storeMeme('user_1', 'a.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_1', 'b.jpg', UploadedFile::fromFile(__FILE__));
        $repo->markAsDeleted($id1);

        $all = $repo->listAllMemes();
        $this->assertCount(1, $all);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::getMemeByOriginalFilename
     */
    public function test_getMemeByOriginalFilename_returns_meme_when_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $repo->storeMeme('user_1', 'norm.jpg', $uploadedFile);

        $meme = $repo->getMemeByOriginalFilename('user_1', $uploadedFile->getOriginalName());
        $this->assertNotNull($meme);
    }

    /**
     * @covers \Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::getMemeByOriginalFilename
     */
    public function test_getMemeByOriginalFilename_returns_null_when_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'norm.jpg', UploadedFile::fromFile(__FILE__));

        $this->assertNull($repo->getMemeByOriginalFilename('user_1', 'other.jpg'));
    }
}
