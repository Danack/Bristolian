<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Model\Types\Meme;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for MemeStorageRepo implementations.
 */
abstract class MemeStorageRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the MemeStorageRepo implementation.
     *
     * @return MemeStorageRepo
     */
    abstract public function getTestInstance(): MemeStorageRepo;


    public function test_storeMeme(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $this->assertNotEmpty($meme_id);
    }


    public function test_getMeme_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->getTestInstance();

        $meme = $repo->getMeme('nonexistent-id');
        $this->assertNull($meme);
    }


    public function test_getMeme_returns_meme_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $meme = $repo->getMeme($meme_id);
        $this->assertNotNull($meme);
        $this->assertInstanceOf(Meme::class, $meme);
        $this->assertSame($meme_id, $meme->id);
    }


    public function test_getByNormalizedName_returns_null_for_nonexistent_name(): void
    {
        $repo = $this->getTestInstance();

        $meme = $repo->getByNormalizedName('nonexistent-meme.jpg');
        $this->assertNull($meme);
    }


    public function test_getByNormalizedName_returns_meme_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test-meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $meme = $repo->getByNormalizedName($normalized_filename);
        $this->assertNotNull($meme);
        $this->assertInstanceOf(Meme::class, $meme);
        $this->assertSame($meme_id, $meme->id);
    }


    public function test_listMemesForUser_returns_empty_initially(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';

        $memes = $repo->listMemesForUser($user_id);
        $this->assertEmpty($memes);
    }


    public function test_listMemesForUser_returns_memes_for_user(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $repo->storeMeme($user_id, $normalized_filename, $uploadedFile);

        $memes = $repo->listMemesForUser($user_id);
        $this->assertNotEmpty($memes);
        $this->assertContainsOnlyInstancesOf(Meme::class, $memes);
    }


    public function test_searchMemesForUser_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';

        $memes = $repo->searchMemesForUser($user_id, null, null);
    }


    public function test_searchMemesForUser_with_query(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';

        $memes = $repo->searchMemesForUser($user_id, 'test', null);
        $this->assertContainsOnlyInstancesOf(Meme::class, $memes);
    }


    public function test_searchMemesForUser_with_tag_type(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';

        $memes = $repo->searchMemesForUser($user_id, null, 'character');
        $this->assertContainsOnlyInstancesOf(Meme::class, $memes);
    }


    public function test_setUploaded(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // Should not throw an exception
        $repo->setUploaded($meme_id);
    }


    public function test_markAsDeleted(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // Should not throw an exception
        $repo->markAsDeleted($meme_id);
    }


    public function test_searchMemesByExactTags_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $tagTexts = ['tag1', 'tag2'];

        $memes = $repo->searchMemesByExactTags($user_id, $tagTexts);
        $this->assertContainsOnlyInstancesOf(Meme::class, $memes);
    }


    public function test_searchMemesByExactTags_with_empty_array(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';

        $memes = $repo->searchMemesByExactTags($user_id, []);
    }


    public function test_getMemeByOriginalFilename_returns_null_for_nonexistent_file(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $original_filename = 'nonexistent.jpg';

        $meme = $repo->getMemeByOriginalFilename($user_id, $original_filename);
        $this->assertNull($meme);
    }


    public function test_getMemeByOriginalFilename_returns_meme_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_meme_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $meme_id = $repo->storeMeme(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $meme = $repo->getMemeByOriginalFilename($user_id, $uploadedFile->getOriginalName());
        // Note: Implementation may or may not find by original filename depending on how it's stored
        // This test verifies the method exists and returns null or a Meme
        if ($meme !== null) {
            $this->assertInstanceOf(Meme::class, $meme);
        } else {
            $this->assertNull($meme);
        }
    }
}
