<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AvatarImageStorageInfoRepo;

use Bristolian\Model\Types\AvatarImageFile;
use Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * Abstract test class for AvatarImageStorageInfoRepo implementations.
 *
 * @internal
 * @coversNothing
 */
abstract class AvatarImageStorageInfoRepoFixture extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * Get a test instance of the AvatarImageStorageInfoRepo implementation.
     *
     * @return AvatarImageStorageInfoRepo
     */
    abstract public function getTestInstance(): AvatarImageStorageInfoRepo;

    /**
     * Get a test user ID. Override in PDO tests to create actual user.
     */
    protected function getTestUserId(): string
    {
        return 'user_123';
    }

    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::__construct
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::storeFileInfo
     */
    public function test_storeFileInfo(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $normalized_filename = 'test_file_' . time() . '.png';
        $uploadedFile = new UploadedFile(
            '/tmp/test.png',
            1024,
            'test.png',
            0
        );

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $this->assertNotEmpty($file_id);
        /** @phpstan-ignore-next-line method.alreadyNarrowedType */
        $this->assertIsString($file_id);
    }


    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::getById
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::getById
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::getById
     */
    public function test_getById_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->getTestInstance();

        $file = $repo->getById('nonexistent-id');
        $this->assertNull($file);
    }


    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::getById
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::getById
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::getById
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::storeFileInfo
     */
    public function test_getById_returns_file_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $normalized_filename = 'test_file_' . time() . '.png';
        $uploadedFile = new UploadedFile(
            '/tmp/test.png',
            1024,
            'test.png',
            0
        );

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $file = $repo->getById($file_id);
        $this->assertNotNull($file);
        $this->assertInstanceOf(AvatarImageFile::class, $file);
        $this->assertSame($file_id, $file->id);
    }


    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::getByNormalizedName
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::getByNormalizedName
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::getByNormalizedName
     */
    public function test_getByNormalizedName_returns_null_for_nonexistent_name(): void
    {
        $repo = $this->getTestInstance();

        $file = $repo->getByNormalizedName('nonexistent-file.png');
        $this->assertNull($file);
    }


    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::getByNormalizedName
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::getByNormalizedName
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::getByNormalizedName
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::storeFileInfo
     */
    public function test_getByNormalizedName_returns_file_after_storing(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $normalized_filename = 'test-file_' . time() . '.png';
        $uploadedFile = new UploadedFile(
            '/tmp/test.png',
            1024,
            'test.png',
            0
        );

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        $file = $repo->getByNormalizedName($normalized_filename);
        $this->assertNotNull($file);
        $this->assertInstanceOf(AvatarImageFile::class, $file);
        $this->assertSame($file_id, $file->id);
        $this->assertSame($normalized_filename, $file->normalized_name);
    }


    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::setUploaded
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::setUploaded
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::storeFileInfo
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::setUploaded
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::storeFileInfo
     */
    public function test_setUploaded(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getTestUserId();
        $normalized_filename = 'test_file_' . time() . '.png';
        $uploadedFile = new UploadedFile(
            '/tmp/test.png',
            1024,
            'test.png',
            0
        );

        $file_id = $repo->storeFileInfo(
            $user_id,
            $normalized_filename,
            $uploadedFile
        );

        // Should not throw an exception
        $repo->setUploaded($file_id);
    }
}
