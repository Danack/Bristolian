<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTextRepo;

use Bristolian\Model\Generated\MemeText;
use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Types\Meme;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeFileState;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * Abstract test class for MemeTextRepo implementations.
 */
abstract class MemeTextRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the MemeTextRepo implementation.
     *
     * @return MemeTextRepo
     */
    abstract public function getTestInstance(): MemeTextRepo;

    /**
     * Get a MemeStorageRepo instance for testing.
     * This is used by tests that need to create memes.
     * Concrete test classes should override this if needed.
     */
    protected function getMemeStorageRepo(): MemeStorageRepo
    {
        return new FakeMemeStorageRepo();
    }

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user-123';
    }

    public function test_getNextMemeToOCR_returns_null_when_no_memes(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getNextMemeToOCR();

        $this->assertNull($result);
    }

    public function test_getNextMemeToOCR_returns_meme_without_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

        $result = $repo->getNextMemeToOCR();

        $this->assertInstanceOf(StoredMeme::class, $result);
        $this->assertSame($meme_id, $result->id);
    }

    public function test_getNextMemeToOCR_returns_oldest_meme_first(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create multiple memes
        $uploadedFile1 = UploadedFile::fromFile(__FILE__);
        $meme_id1 = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme1.jpg',
            $uploadedFile1
        );

        // Small delay to ensure different timestamps
        usleep(1000);

        $uploadedFile2 = UploadedFile::fromFile(__FILE__);
        $meme_id2 = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme2.jpg',
            $uploadedFile2
        );

        $result = $repo->getNextMemeToOCR();

        $this->assertInstanceOf(StoredMeme::class, $result);
        $this->assertSame($meme_id1, $result->id); // Oldest should be first
    }

    public function test_getNextMemeToOCR_excludes_deleted_memes(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme and mark it as deleted
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );
        $memeStorageRepo->markAsDeleted($meme_id);

        $result = $repo->getNextMemeToOCR();

        $this->assertNull($result);
    }

    public function test_getNextMemeToOCR_excludes_memes_with_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

        // Get the meme as StoredMeme
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

        // Save text for the meme
        $repo->saveMemeText($storedMeme, 'Some text');

        // Should return null since meme now has text
        $result = $repo->getNextMemeToOCR();

        $this->assertNull($result);
    }

    public function test_saveMemeText_stores_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

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

        // Should not throw exception
        $repo->saveMemeText($storedMeme, 'Found text from OCR');

        $this->assertTrue(true);
    }

    public function test_getMemeText_returns_null_for_nonexistent_meme(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getMemeText('nonexistent-meme-id');

        $this->assertNull($result);
    }

    public function test_getMemeText_returns_saved_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

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

        $repo->saveMemeText($storedMeme, 'Found text');

        $result = $repo->getMemeText($meme_id);

        $this->assertInstanceOf(MemeText::class, $result);
        $this->assertSame('Found text', $result->text);
        $this->assertSame($meme_id, $result->meme_id);
    }

    public function test_searchMemeIdsByText_returns_empty_array_when_no_matches(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->searchMemeIdsByText($this->getTestUserId(), 'search term');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_searchMemeIdsByText_finds_memes_by_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create memes
        $uploadedFile1 = UploadedFile::fromFile(__FILE__);
        $meme_id1 = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme1.jpg',
            $uploadedFile1
        );

        $uploadedFile2 = UploadedFile::fromFile(__FILE__);
        $meme_id2 = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme2.jpg',
            $uploadedFile2
        );

        $meme1 = $memeStorageRepo->getMeme($meme_id1);
        $storedMeme1 = new StoredMeme(
            id: $meme1->id,
            normalized_name: $meme1->normalized_name,
            original_filename: $meme1->original_filename,
            state: $meme1->state,
            size: $meme1->size,
            user_id: $meme1->user_id,
            created_at: $meme1->created_at,
            deleted: $meme1->deleted ? 1 : 0,
        );

        $meme2 = $memeStorageRepo->getMeme($meme_id2);
        $storedMeme2 = new StoredMeme(
            id: $meme2->id,
            normalized_name: $meme2->normalized_name,
            original_filename: $meme2->original_filename,
            state: $meme2->state,
            size: $meme2->size,
            user_id: $meme2->user_id,
            created_at: $meme2->created_at,
            deleted: $meme2->deleted ? 1 : 0,
        );

        $repo->saveMemeText($storedMeme1, 'Hello world');
        $repo->saveMemeText($storedMeme2, 'Goodbye world');

        $result = $repo->searchMemeIdsByText('user-123', 'world');

        $this->assertCount(2, $result);
        $this->assertContains($meme_id1, $result);
        $this->assertContains($meme_id2, $result);
    }

    public function test_searchMemeIdsByText_is_case_insensitive(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

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

        $repo->saveMemeText($storedMeme, 'Hello World');

        $result = $repo->searchMemeIdsByText('user-123', 'hello');

        $this->assertCount(1, $result);
        $this->assertContains($meme_id, $result);
    }

    public function test_searchMemeIdsByText_only_returns_memes_for_specified_user(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create memes for different users
        $uploadedFile1 = UploadedFile::fromFile(__FILE__);
        $meme_id1 = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme1.jpg',
            $uploadedFile1
        );

        $uploadedFile2 = UploadedFile::fromFile(__FILE__);
        $meme_id2 = $memeStorageRepo->storeMeme(
            'user-456',
            'test_meme2.jpg',
            $uploadedFile2
        );

        $meme1 = $memeStorageRepo->getMeme($meme_id1);
        $storedMeme1 = new StoredMeme(
            id: $meme1->id,
            normalized_name: $meme1->normalized_name,
            original_filename: $meme1->original_filename,
            state: $meme1->state,
            size: $meme1->size,
            user_id: $meme1->user_id,
            created_at: $meme1->created_at,
            deleted: $meme1->deleted ? 1 : 0,
        );

        $meme2 = $memeStorageRepo->getMeme($meme_id2);
        $storedMeme2 = new StoredMeme(
            id: $meme2->id,
            normalized_name: $meme2->normalized_name,
            original_filename: $meme2->original_filename,
            state: $meme2->state,
            size: $meme2->size,
            user_id: $meme2->user_id,
            created_at: $meme2->created_at,
            deleted: $meme2->deleted ? 1 : 0,
        );

        $repo->saveMemeText($storedMeme1, 'User 123 text');
        $repo->saveMemeText($storedMeme2, 'User 456 text');

        $result = $repo->searchMemeIdsByText('user-123', 'text');

        $this->assertCount(1, $result);
        $this->assertContains($meme_id1, $result);
        $this->assertNotContains($meme_id2, $result);
    }

    public function test_updateMemeText_creates_text_if_not_exists(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

        // Should not throw exception
        $repo->updateMemeText($meme_id, 'Updated text');

        $result = $repo->getMemeText($meme_id);
        $this->assertInstanceOf(MemeText::class, $result);
        $this->assertSame('Updated text', $result->text);
    }

    public function test_updateMemeText_updates_existing_text(): void
    {
        $memeStorageRepo = $this->getMemeStorageRepo();
        $repo = $this->getTestInstance();

        // Create a meme
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $meme_id = $memeStorageRepo->storeMeme(
            $this->getTestUserId(),
            'test_meme.jpg',
            $uploadedFile
        );

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

        $repo->saveMemeText($storedMeme, 'Original text');
        $repo->updateMemeText($meme_id, 'Updated text');

        $result = $repo->getMemeText($meme_id);
        $this->assertInstanceOf(MemeText::class, $result);
        $this->assertSame('Updated text', $result->text);
    }
}
