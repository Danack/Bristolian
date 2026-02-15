<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Model\Generated\StairImageObjectInfo as BristolStairImageFile;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for BristolStairImageStorageInfoRepo implementations.
 *
 * @coversNothing
 */
abstract class BristolStairImageStorageInfoRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the BristolStairImageStorageInfoRepo implementation.
     *
     * @return BristolStairImageStorageInfoRepo
     */
    abstract public function getTestInstance(): BristolStairImageStorageInfoRepo;


    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo::__construct
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\PdoBristolStairImageStorageInfoRepo::storeFileInfo
     */
    public function test_storeFileInfo(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $this->assertNotEmpty($file_id);
        /** @phpstan-ignore-next-line method.alreadyNarrowedType */
        $this->assertIsString($file_id);
    }


    public function test_getById_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->getTestInstance();

        $file = $repo->getById('nonexistent-id');
        $this->assertNull($file);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::getById
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::storeFileInfo
     */
    public function test_getById_returns_file_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $file = $repo->getById($file_id);
        $this->assertNotNull($file);
        $this->assertInstanceOf(BristolStairImageFile::class, $file);
        $this->assertSame($file_id, $file->id);
    }


    public function test_getByNormalizedName_returns_null_for_nonexistent_name(): void
    {
        $repo = $this->getTestInstance();

        $file = $repo->getByNormalizedName('nonexistent-file.jpg');
        $this->assertNull($file);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::getByNormalizedName
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::storeFileInfo
     */
    public function test_getByNormalizedName_returns_file_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test-file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $file = $repo->getByNormalizedName($normalized_filename);
        $this->assertNotNull($file);
        $this->assertInstanceOf(BristolStairImageFile::class, $file);
        $this->assertSame($file_id, $file->id);
        $this->assertSame($normalized_filename, $file->normalized_name);
    }


    /**
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::setUploaded
     * @covers \Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo::storeFileInfo
     */
    public function test_setUploaded(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $normalized_filename = 'test_file_' . time() . '.jpg';
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // Should not throw an exception
        $repo->setUploaded($file_id);
    }
}
