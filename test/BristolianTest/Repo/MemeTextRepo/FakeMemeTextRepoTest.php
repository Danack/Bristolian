<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTextRepo;

use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Types\Meme;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeMemeTextRepoTest extends MemeTextRepoFixture
{
    private ?FakeMemeStorageRepo $memeStorageRepo = null;

    public function getTestInstance(): MemeTextRepo
    {
        return new FakeMemeTextRepo($this->getMemeStorageRepo());
    }

    protected function getMemeStorageRepo(): MemeStorageRepo
    {
        if ($this->memeStorageRepo === null) {
            $this->memeStorageRepo = new FakeMemeStorageRepo();
        }
        return $this->memeStorageRepo;
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::getNextMemeToOCR
     */
    public function test_getNextMemeToOCR_returns_null_when_no_memes(): void
    {
        $repo = new FakeMemeTextRepo(new FakeMemeStorageRepo());
        $this->assertNull($repo->getNextMemeToOCR());
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::getNextMemeToOCR
     */
    public function test_getNextMemeToOCR_returns_null_when_storage_has_no_getStoredMeme(): void
    {
        $repo = new FakeMemeTextRepo(new MemeStorageRepoStubWithoutGetStoredMeme());
        $this->assertNull($repo->getNextMemeToOCR());
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::getNextMemeToOCR
     */
    public function test_getNextMemeToOCR_returns_meme_when_meme_exists_without_text(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme($this->getTestUserId(), 'test_meme.jpg', $uploadedFile);
        $repo = new FakeMemeTextRepo($memeStorageRepo);

        $result = $repo->getNextMemeToOCR();

        $this->assertInstanceOf(StoredMeme::class, $result);
        $this->assertSame($meme_id, $result->id);
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::getNextMemeToOCR
     */
    public function test_getNextMemeToOCR_returns_oldest_first(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile1 = UploadedFile::fromFile(__FILE__);
        $meme_id1 = $memeStorageRepo->storeMeme($this->getTestUserId(), 'test1.jpg', $uploadedFile1);
        usleep(2000);
        $uploadedFile2 = UploadedFile::fromFile(__FILE__);
        $meme_id2 = $memeStorageRepo->storeMeme($this->getTestUserId(), 'test2.jpg', $uploadedFile2);
        $repo = new FakeMemeTextRepo($memeStorageRepo);

        $result = $repo->getNextMemeToOCR();

        $this->assertNotNull($result);
        $this->assertSame($meme_id1, $result->id);
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::getNextMemeToOCR
     */
    public function test_getNextMemeToOCR_excludes_deleted_memes(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme($this->getTestUserId(), 'test_meme.jpg', $uploadedFile);
        $memeStorageRepo->markAsDeleted($meme_id);
        $repo = new FakeMemeTextRepo($memeStorageRepo);

        $this->assertNull($repo->getNextMemeToOCR());
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::getNextMemeToOCR
     */
    public function test_getNextMemeToOCR_excludes_memes_with_existing_text(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme($this->getTestUserId(), 'test_meme.jpg', $uploadedFile);
        $meme = $memeStorageRepo->getMeme($meme_id);
        $storedMeme = new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
        $repo = new FakeMemeTextRepo($memeStorageRepo);
        $repo->saveMemeText($storedMeme, 'Some text');

        $this->assertNull($repo->getNextMemeToOCR());
    }

    /**
     * @covers \Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo::searchMemeIdsByText
     */
    public function test_searchMemeIdsByText_excludes_deleted_memes(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme($this->getTestUserId(), 'test_meme.jpg', $uploadedFile);
        $meme = $memeStorageRepo->getMeme($meme_id);
        $storedMeme = new StoredMeme(
            id: $meme->id,
            normalized_name: $meme->normalized_name,
            original_filename: $meme->original_filename,
            state: $meme->state,
            size: $meme->size,
            user_id: $meme->user_id,
            created_at: $meme->created_at,
            deleted: $meme->deleted ? 1 : 0,
        );
        $repo = new FakeMemeTextRepo($memeStorageRepo);
        $repo->saveMemeText($storedMeme, 'Unique searchable text');
        $memeStorageRepo->markAsDeleted($meme_id);

        $result = $repo->searchMemeIdsByText($this->getTestUserId(), 'Unique searchable');
        $this->assertEmpty($result);
    }
}

/**
 * MemeStorageRepo implementation without getStoredMeme() - used to test FakeMemeTextRepo's defensive path.
 *
 * @internal
 * @coversNothing
 */
class MemeStorageRepoStubWithoutGetStoredMeme implements MemeStorageRepo
{
    public function storeMeme(string $user_id, string $normalized_filename, UploadedFile $uploadedFile): string
    {
        return 'stub-id';
    }

    public function getMeme(string $id): Meme|null
    {
        return null;
    }

    public function getByNormalizedName(string $normalized_name): Meme|null
    {
        return null;
    }

    public function listMemesForUser(string $user_id): array
    {
        return [];
    }

    public function listMemesForUserWithNoTags(string $user_id): array
    {
        return [];
    }

    public function listAllMemes(): array
    {
        return [];
    }

    public function searchMemesForUser(string $user_id, ?string $query, ?string $tag_type): array
    {
        return [];
    }

    public function setUploaded(string $meme_id): void
    {
    }

    public function markAsDeleted(string $meme_id): void
    {
    }

    public function searchMemesByExactTags(string $user_id, array $tagTexts): array
    {
        return [];
    }

    public function getMemeByOriginalFilename(string $user_id, string $original_filename): Meme|null
    {
        return null;
    }
}
