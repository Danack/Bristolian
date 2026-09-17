<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\UploadedFiles\UploadedFile;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * @group standard_repo
 */

#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'getByNormalizedName')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'getMeme')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'getMemeByOriginalFilename')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'getStoredMeme')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'listAllMemes')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'listMemesForUser')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'listMemesForUserWithNoTags')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'markAsDeleted')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'searchMemesByExactTags')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'searchMemesForUser')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'setUploaded')]
#[CoversMethod(\Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo::class, 'storeMeme')]

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

    public function test_setUploaded_updates_state(): void
    {
        $repo = new FakeMemeStorageRepo();
        $meme_id = $repo->storeMeme('user_1', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo->setUploaded($meme_id);
        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertSame(\Bristolian\Repo\MemeStorageRepo\MemeFileState::UPLOADED->value, $meme->state);
    }

    public function test_markAsDeleted_updates_meme(): void
    {
        $repo = new FakeMemeStorageRepo();
        $meme_id = $repo->storeMeme('user_1', 'norm.jpg', UploadedFile::fromFile(__FILE__));
        $repo->markAsDeleted($meme_id);
        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertTrue($meme->deleted);
    }

    public function test_listAllMemes_returns_non_deleted_memes(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'a.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_1', 'b.jpg', UploadedFile::fromFile(__FILE__));

        $all = $repo->listAllMemes();
        $this->assertCount(2, $all);
    }

    public function test_listMemesForUserWithNoTags_delegates_to_listMemesForUser(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'x.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->listMemesForUserWithNoTags('user_1');
        $this->assertCount(1, $memes);
    }

    public function test_searchMemesForUser_returns_memes_for_user(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'f.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->searchMemesForUser('user_1', null, null);
        $this->assertCount(1, $memes);
    }

    public function test_searchMemesByExactTags_empty_returns_listMemesForUser(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'f.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->searchMemesByExactTags('user_1', []);
        $this->assertCount(1, $memes);
    }

    public function test_searchMemesByExactTags_with_tags_returns_user_memes(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'f.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->searchMemesByExactTags('user_1', ['tag1']);
        $this->assertCount(1, $memes);
    }

    public function test_getStoredMeme_returns_all_stored(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'one.jpg', UploadedFile::fromFile(__FILE__));

        $stored = $repo->getStoredMeme();
        $this->assertCount(1, $stored);
    }

    public function test_setUploaded_throws_when_meme_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('meme not found to set as uploaded.');

        $repo->setUploaded('nonexistent-id');
    }

    public function test_markAsDeleted_throws_when_meme_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage('meme not found to mark as deleted.');

        $repo->markAsDeleted('nonexistent-id');
    }

    public function test_getMeme_returns_stored_meme(): void
    {
        $repo = new FakeMemeStorageRepo();
        $meme_id = $repo->storeMeme('user_1', 'g.jpg', UploadedFile::fromFile(__FILE__));

        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertSame($meme_id, $meme->id);
    }

    public function test_getByNormalizedName_returns_meme_when_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'normalized.jpg', UploadedFile::fromFile(__FILE__));

        $meme = $repo->getByNormalizedName('normalized.jpg');
        $this->assertNotNull($meme);
        $this->assertSame('normalized.jpg', $meme->normalized_name);
    }

    public function test_getByNormalizedName_returns_null_when_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'other.jpg', UploadedFile::fromFile(__FILE__));

        $this->assertNull($repo->getByNormalizedName('nonexistent.jpg'));
    }

    public function test_storeMeme_creates_meme_and_returns_id(): void
    {
        $repo = new FakeMemeStorageRepo();
        $id = $repo->storeMeme('user_1', 'stored.jpg', UploadedFile::fromFile(__FILE__));

        $this->assertNotEmpty($id);
        $meme = $repo->getMeme($id);
        $this->assertSame('user_1', $meme->user_id);
        $this->assertSame('stored.jpg', $meme->normalized_name);
    }

    public function test_listMemesForUser_returns_only_that_users_memes(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'a.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_1', 'b.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_2', 'c.jpg', UploadedFile::fromFile(__FILE__));

        $memes = $repo->listMemesForUser('user_1');
        $this->assertCount(2, $memes);
    }

    public function test_listAllMemes_excludes_deleted(): void
    {
        $repo = new FakeMemeStorageRepo();
        $id1 = $repo->storeMeme('user_1', 'a.jpg', UploadedFile::fromFile(__FILE__));
        $repo->storeMeme('user_1', 'b.jpg', UploadedFile::fromFile(__FILE__));
        $repo->markAsDeleted($id1);

        $all = $repo->listAllMemes();
        $this->assertCount(1, $all);
    }

    public function test_getMemeByOriginalFilename_returns_meme_when_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $repo->storeMeme('user_1', 'norm.jpg', $uploadedFile);

        $meme = $repo->getMemeByOriginalFilename('user_1', $uploadedFile->getOriginalName());
        $this->assertNotNull($meme);
    }

    public function test_getMemeByOriginalFilename_returns_null_when_not_found(): void
    {
        $repo = new FakeMemeStorageRepo();
        $repo->storeMeme('user_1', 'norm.jpg', UploadedFile::fromFile(__FILE__));

        $this->assertNull($repo->getMemeByOriginalFilename('user_1', 'other.jpg'));
    }
}
