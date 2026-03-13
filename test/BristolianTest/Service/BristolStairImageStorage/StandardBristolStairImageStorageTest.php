<?php

declare(strict_types=1);

namespace BristolianTest\Service\BristolStairImageStorage;

use Bristolian\Model\Generated\BristolStairInfo;
use Bristolian\Parameters\BristolStairsGpsParams;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairsRepo\FakeBristolStairsRepo;
use Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class StandardBristolStairImageStorageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::__construct
     * @covers \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::storeFileForUser
     */
    public function test_storeFileForUser_returns_BristolStairInfo_and_uploads_to_object_store(): void
    {
        $stairImageStorageInfoRepo = new FakeBristolStairImageStorageInfoRepo();
        $objectStore = new FakeBristolianStairImageObjectStore();
        $bristolStairsRepo = new FakeBristolStairsRepo();

        $storage = new StandardBristolStairImageStorage(
            $stairImageStorageInfoRepo,
            $objectStore,
            $bristolStairsRepo
        );

        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/stairs/stairs_test_b_9.jpeg');
        $gpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap([
            'gps_latitude' => 51.4545,
            'gps_longitude' => -2.5879,
        ]));

        $result = $storage->storeFileForUser(
            'user_1',
            $uploadedFile,
            ['jpg', 'jpeg'],
            $gpsParams
        );

        $this->assertInstanceOf(BristolStairInfo::class, $result);
        $this->assertNotEmpty($result->stored_stair_image_file_id);
        $this->assertIsFloat($result->latitude);
        $this->assertIsFloat($result->longitude);

        $storedFileInfo = $stairImageStorageInfoRepo->getStoredFileInfo();
        $this->assertCount(1, $storedFileInfo);
        $fileInfo = array_values($storedFileInfo)[0];
        $this->assertStringEndsWith('.jpeg', $fileInfo->normalized_name);
        $this->assertTrue($objectStore->hasFile($fileInfo->normalized_name));
    }

    /**
     * @covers \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::storeFileForUser
     */
    public function test_storeFileForUser_converts_heic_to_jpg_and_returns_BristolStairInfo(): void
    {
        $heicPath = __DIR__ . '/../../../fixtures/images/the_wrongest_thing.heic';
        if (!is_file($heicPath)) {
            $this->markTestSkipped('HEIC fixture not found: ' . $heicPath);
        }
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension required for HEIC conversion');
        }

        $stairImageStorageInfoRepo = new FakeBristolStairImageStorageInfoRepo();
        $objectStore = new FakeBristolianStairImageObjectStore();
        $bristolStairsRepo = new FakeBristolStairsRepo();

        $storage = new StandardBristolStairImageStorage(
            $stairImageStorageInfoRepo,
            $objectStore,
            $bristolStairsRepo
        );

        $uploadedFile = UploadedFile::fromFile($heicPath);
        $gpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap([
            'gps_latitude' => 51.4545,
            'gps_longitude' => -2.5879,
        ]));

        $result = $storage->storeFileForUser(
            'user_1',
            $uploadedFile,
            ['heic', 'jpg', 'jpeg'],
            $gpsParams
        );

        $this->assertInstanceOf(BristolStairInfo::class, $result);
        $this->assertNotEmpty($result->stored_stair_image_file_id);

        $storedFileInfo = $stairImageStorageInfoRepo->getStoredFileInfo();
        $this->assertCount(1, $storedFileInfo);
        $fileInfo = array_values($storedFileInfo)[0];
        $this->assertStringEndsWith('.jpg', $fileInfo->normalized_name);
        $this->assertTrue($objectStore->hasFile($fileInfo->normalized_name));
    }

    /**
     * @covers \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::storeFileForUser
     */
    public function test_storeFileForUser_returns_UploadError_when_extension_not_allowed(): void
    {
        $storage = new StandardBristolStairImageStorage(
            new FakeBristolStairImageStorageInfoRepo(),
            new FakeBristolianStairImageObjectStore(),
            new FakeBristolStairsRepo()
        );

        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../sample.pdf');
        $gpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap([]));

        $result = $storage->storeFileForUser(
            'user_1',
            $uploadedFile,
            ['jpg', 'jpeg'],
            $gpsParams
        );

        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error_message);
    }

    /**
     * Unreadable path: file_get_contents fails. Skipped when chmod 0o000 does not
     * prevent read (e.g. process is owner in some environments).
     *
     * @covers \Bristolian\Service\BristolStairImageStorage\StandardBristolStairImageStorage::storeFileForUser
     */
    public function test_storeFileForUser_returns_UploadError_when_file_unreadable(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'stair_test_') . '.txt';
        $this->assertNotFalse(file_put_contents($tempFile, 'content'));
        \Safe\chmod($tempFile, 0o000);

        try {
            if (@file_get_contents($tempFile) !== false) {
                $this->markTestSkipped('chmod 0o000 does not prevent read in this environment');
            }

            $storage = new StandardBristolStairImageStorage(
                new FakeBristolStairImageStorageInfoRepo(),
                new FakeBristolianStairImageObjectStore(),
                new FakeBristolStairsRepo()
            );

            $uploadedFile = UploadedFile::fromFile($tempFile);
            $gpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap([]));

            $result = $storage->storeFileForUser(
                'user_1',
                $uploadedFile,
                ['txt'],
                $gpsParams
            );

            $this->assertInstanceOf(UploadError::class, $result);
            $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $result->error_message);
        } finally {
            @chmod($tempFile, 0o600);
            @unlink($tempFile);
        }
    }
}
