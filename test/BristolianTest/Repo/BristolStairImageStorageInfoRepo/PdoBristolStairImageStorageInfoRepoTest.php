<?php

namespace BristolianTest\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Repo\BristolStairImageStorageInfoRepo\FileState;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\Model\Generated\StairImageObjectInfo as BristolStairImageFile;
use Ramsey\Uuid\Uuid;

/**
 * @group db
 * @coversNothing
 */
class PdoBristolStairImageStorageInfoRepoTest extends BristolStairImageStorageInfoRepoFixture
{
    use TestPlaceholders;

    public function getTestInstance(): BristolStairImageStorageInfoRepo
    {
        return $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_storeFileInfo_creates_new_record(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        $this->assertNotEmpty($file_id);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_getById_returns_file_info(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        $file_info = $repo->getById($file_id);

        $this->assertInstanceOf(BristolStairImageFile::class, $file_info);
        $this->assertSame($file_id, $file_info->id);
        $this->assertSame($user->getUserId(), $file_info->user_id);
        $this->assertSame($normalized_filename, $file_info->normalized_name);
        $this->assertSame(FileState::INITIAL->value, $file_info->state);
        $this->assertInstanceOf(\DateTimeInterface::class, $file_info->created_at);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_getById_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);

        $file_info = $repo->getById('nonexistent-id-' . time());

        $this->assertNull($file_info);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_getByNormalizedName_returns_file_info(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        $file_info = $repo->getByNormalizedName($normalized_filename);

        $this->assertInstanceOf(BristolStairImageFile::class, $file_info);
        $this->assertSame($file_id, $file_info->id);
        $this->assertSame($normalized_filename, $file_info->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_getByNormalizedName_returns_null_for_nonexistent_name(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);

        $file_info = $repo->getByNormalizedName('nonexistent-file-' . time() . '.jpg');

        $this->assertNull($file_info);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_setUploaded_updates_state(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        // Verify initial state
        $file_info_before = $repo->getById($file_id);
        $this->assertSame(FileState::INITIAL->value, $file_info_before->state);

        // Set to uploaded
        $repo->setUploaded($file_id);

        // Verify state changed
        $file_info_after = $repo->getById($file_id);
        $this->assertSame(FileState::UPLOADED->value, $file_info_after->state);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_setUploaded_throws_exception_for_nonexistent_id(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Failed to update uploaded file.");

        $repo->setUploaded('nonexistent-id-' . time());
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_file_info_properties(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        $file_info = $repo->getById($file_id);

        // Verify all properties
        $this->assertIsInt($file_info->size);
        $this->assertInstanceOf(\DateTimeInterface::class, $file_info->created_at);

        // Verify specific values
        $this->assertSame($file_id, $file_info->id);
        $this->assertSame($user->getUserId(), $file_info->user_id);
        $this->assertSame($normalized_filename, $file_info->normalized_name);
        $this->assertGreaterThan(0, $file_info->size);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_multiple_files_for_same_user(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uploadedFile = UploadedFile::fromFile(__FILE__);

        // Create multiple files for the same user
        $uuid1 = Uuid::uuid7();
        $normalized_filename1 = $uuid1->toString() . '.jpg';
        $file_id1 = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename1,
            $uploadedFile
        );

        $uuid2 = Uuid::uuid7();
        $normalized_filename2 = $uuid2->toString() . '.png';
        $file_id2 = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename2,
            $uploadedFile
        );

        // Verify both files exist independently
        $file1 = $repo->getById($file_id1);
        $file2 = $repo->getById($file_id2);

        $this->assertNotSame($file1->id, $file2->id);
        $this->assertSame($user->getUserId(), $file1->user_id);
        $this->assertSame($user->getUserId(), $file2->user_id);
        $this->assertSame($normalized_filename1, $file1->normalized_name);
        $this->assertSame($normalized_filename2, $file2->normalized_name);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_files_from_different_users(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user1 = $this->createTestAdminUser();
        $user2 = $this->createTestAdminUser();

        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $uuid1 = Uuid::uuid7();
        $normalized_filename1 = $uuid1->toString() . '.jpg';
        $file_id1 = $repo->storeFileInfo(
            $user1->getUserId(),
            $normalized_filename1,
            $uploadedFile
        );

        $uuid2 = Uuid::uuid7();
        $normalized_filename2 = $uuid2->toString() . '.jpg';
        $file_id2 = $repo->storeFileInfo(
            $user2->getUserId(),
            $normalized_filename2,
            $uploadedFile
        );

        $file1 = $repo->getById($file_id1);
        $file2 = $repo->getById($file_id2);

        $this->assertSame($user1->getUserId(), $file1->user_id);
        $this->assertSame($user2->getUserId(), $file2->user_id);
        $this->assertNotSame($file1->user_id, $file2->user_id);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_created_at_is_recent(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $before = new \DateTimeImmutable();
        
        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        $after = new \DateTimeImmutable();

        $file_info = $repo->getById($file_id);

        // Verify created_at is between before and after
        $this->assertGreaterThanOrEqual(
            $before->getTimestamp(),
            $file_info->created_at->getTimestamp()
        );
        $this->assertLessThanOrEqual(
            $after->getTimestamp(),
            $file_info->created_at->getTimestamp()
        );
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_complete_workflow(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        // Step 1: Store file info
        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        // Step 2: Retrieve by ID
        $file_by_id = $repo->getById($file_id);
        $this->assertNotNull($file_by_id);
        $this->assertSame(FileState::INITIAL->value, $file_by_id->state);

        // Step 3: Retrieve by normalized name
        $file_by_name = $repo->getByNormalizedName($normalized_filename);
        $this->assertNotNull($file_by_name);
        $this->assertSame($file_id, $file_by_name->id);

        // Step 4: Mark as uploaded
        $repo->setUploaded($file_id);

        // Step 5: Verify state changed
        $file_uploaded = $repo->getById($file_id);
        $this->assertSame(FileState::UPLOADED->value, $file_uploaded->state);
    }

    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo
     */
    public function test_original_filename_preserved(): void
    {
        $repo = $this->injector->make(PdoBristolStairImageStorageInfoRepo::class);
        $user = $this->createTestAdminUser();

        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user->getUserId(),
            $normalized_filename,
            $uploadedFile
        );

        $file_info = $repo->getById($file_id);

        // Verify original filename is stored
        $this->assertNotEmpty($file_info->original_filename);
        $this->assertSame($uploadedFile->getOriginalName(), $file_info->original_filename);
    }
}
