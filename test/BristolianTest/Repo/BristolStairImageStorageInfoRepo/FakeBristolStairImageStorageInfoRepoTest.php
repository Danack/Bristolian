<?php

namespace BristolianTest\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\FileState;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\StairImageObjectInfo as BristolStairImageFile;

/**
 * Tests for FakeBristolStairImageStorageInfoRepo
 *
 * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
 */
class FakeBristolStairImageStorageInfoRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_constructor(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();
        $this->assertInstanceOf(FakeBristolStairImageStorageInfoRepo::class, $repo);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_storeFileInfo_creates_new_record(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $user_id = 'user_' . time();
        $normalized_filename = 'test_file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $this->assertNotEmpty($file_id);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_getStoredFileInfo_returns_empty_initially(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $files = $repo->getStoredFileInfo();

        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_getStoredFileInfo_returns_stored_files(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $user_id = 'user_' . time();
        $normalized_filename = 'test_file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $files = $repo->getStoredFileInfo();

        $this->assertCount(1, $files);
        $this->assertArrayHasKey($file_id, $files);
        $this->assertInstanceOf(BristolStairImageFile::class, $files[$file_id]);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_stored_file_properties(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $user_id = 'user_' . time();
        $normalized_filename = 'test_file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $files = $repo->getStoredFileInfo();
        $storedFile = $files[$file_id];

        $this->assertInstanceOf(BristolStairImageFile::class, $storedFile);
        $this->assertSame($file_id, $storedFile->id);
        $this->assertSame($normalized_filename, $storedFile->normalized_name);
        $this->assertSame($uploadedFile->getOriginalName(), $storedFile->original_filename);
        $this->assertSame(FileState::INITIAL->value, $storedFile->state);
        $this->assertSame($uploadedFile->getSize(), $storedFile->size);
        $this->assertSame($user_id, $storedFile->user_id);
        $this->assertInstanceOf(\DateTimeInterface::class, $storedFile->created_at);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_multiple_files_stored(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id1 = $repo->storeFileInfo(
            'user_1',
            'file1.jpg',
            $uploadedFile
        );

        $file_id2 = $repo->storeFileInfo(
            'user_1',
            'file2.jpg',
            $uploadedFile
        );

        $file_id3 = $repo->storeFileInfo(
            'user_2',
            'file3.jpg',
            $uploadedFile
        );

        $files = $repo->getStoredFileInfo();

        $this->assertCount(3, $files);
        $this->assertArrayHasKey($file_id1, $files);
        $this->assertArrayHasKey($file_id2, $files);
        $this->assertArrayHasKey($file_id3, $files);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_getById_returns_file(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $file_id = $repo->storeFileInfo('user_1', 'file1.jpg', $uploadedFile);

        $file = $repo->getById($file_id);

        $this->assertInstanceOf(BristolStairImageFile::class, $file);
        $this->assertSame($file_id, $file->id);
        $this->assertSame('file1.jpg', $file->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_getById_returns_null_for_nonexistent_id(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $file = $repo->getById('nonexistent-id');

        $this->assertNull($file);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_getByNormalizedName_returns_file(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $file_id = $repo->storeFileInfo('user_1', 'test-file.jpg', $uploadedFile);

        $file = $repo->getByNormalizedName('test-file.jpg');

        $this->assertInstanceOf(BristolStairImageFile::class, $file);
        $this->assertSame($file_id, $file->id);
        $this->assertSame('test-file.jpg', $file->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_getByNormalizedName_returns_null_for_nonexistent_name(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $file = $repo->getByNormalizedName('nonexistent-file.jpg');

        $this->assertNull($file);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_setUploaded_updates_state(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $file_id = $repo->storeFileInfo('user_1', 'file1.jpg', $uploadedFile);

        // Verify initial state
        $file_before = $repo->getById($file_id);
        $this->assertSame(FileState::INITIAL->value, $file_before->state);

        // Set to uploaded
        $repo->setUploaded($file_id);

        // Verify state changed
        $file_after = $repo->getById($file_id);
        $this->assertSame(FileState::UPLOADED->value, $file_after->state);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_setUploaded_throws_exception_for_nonexistent_id(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to update uploaded file.");

        $repo->setUploaded('nonexistent-id');
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_each_stored_file_has_unique_id(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id1 = $repo->storeFileInfo('user_1', 'file1.jpg', $uploadedFile);
        $file_id2 = $repo->storeFileInfo('user_1', 'file2.jpg', $uploadedFile);
        $file_id3 = $repo->storeFileInfo('user_1', 'file3.jpg', $uploadedFile);

        // All IDs should be unique
        $this->assertNotSame($file_id1, $file_id2);
        $this->assertNotSame($file_id2, $file_id3);
        $this->assertNotSame($file_id1, $file_id3);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_stored_files_keyed_by_id(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id1 = $repo->storeFileInfo('user_1', 'file1.jpg', $uploadedFile);
        $file_id2 = $repo->storeFileInfo('user_2', 'file2.jpg', $uploadedFile);

        $files = $repo->getStoredFileInfo();

        // Verify the keys match the IDs
        foreach ($files as $key => $storedFile) {
            $this->assertSame($key, $storedFile->id);
        }
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_created_at_timestamp_is_recent(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $before = new \DateTimeImmutable();
        
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $file_id = $repo->storeFileInfo('user_1', 'file1.jpg', $uploadedFile);
        
        $after = new \DateTimeImmutable();

        $files = $repo->getStoredFileInfo();
        $storedFile = $files[$file_id];

        // Verify created_at is between before and after
        $this->assertGreaterThanOrEqual(
            $before->getTimestamp(),
            $storedFile->created_at->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $after->getTimestamp(),
            $storedFile->created_at->getTimestamp()
        );
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_setUploaded_preserves_other_properties(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $file_id = $repo->storeFileInfo('user_1', 'file1.jpg', $uploadedFile);

        $file_before = $repo->getById($file_id);
        $original_id = $file_before->id;
        $original_user_id = $file_before->user_id;
        $original_normalized_name = $file_before->normalized_name;
        $original_filename = $file_before->original_filename;
        $original_size = $file_before->size;
        $original_created_at = $file_before->created_at;

        $repo->setUploaded($file_id);

        $file_after = $repo->getById($file_id);
        
        // Verify all other properties unchanged
        $this->assertSame($original_id, $file_after->id);
        $this->assertSame($original_user_id, $file_after->user_id);
        $this->assertSame($original_normalized_name, $file_after->normalized_name);
        $this->assertSame($original_filename, $file_after->original_filename);
        $this->assertSame($original_size, $file_after->size);
        $this->assertSame($original_created_at, $file_after->created_at);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo
     */
    public function test_complete_workflow(): void
    {
        $repo = new FakeBristolStairImageStorageInfoRepo();

        $uploadedFile = UploadedFile::fromFile(__FILE__);

        // Step 1: Store file
        $file_id = $repo->storeFileInfo('user_1', 'workflow-test.jpg', $uploadedFile);
        $this->assertNotEmpty($file_id);

        // Step 2: Get by ID
        $file_by_id = $repo->getById($file_id);
        $this->assertNotNull($file_by_id);
        $this->assertSame(FileState::INITIAL->value, $file_by_id->state);

        // Step 3: Get by normalized name
        $file_by_name = $repo->getByNormalizedName('workflow-test.jpg');
        $this->assertNotNull($file_by_name);
        $this->assertSame($file_id, $file_by_name->id);

        // Step 4: Set uploaded
        $repo->setUploaded($file_id);

        // Step 5: Verify state changed
        $file_uploaded = $repo->getById($file_id);
        $this->assertSame(FileState::UPLOADED->value, $file_uploaded->state);

        // Step 6: Verify it's still in getStoredFileInfo
        $files = $repo->getStoredFileInfo();
        $this->assertArrayHasKey($file_id, $files);
    }
}
