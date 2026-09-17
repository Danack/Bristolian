<?php

declare(strict_types=1);

namespace BristolianTest\Service\BristolStairImageStorage;

use BristolianGenerated\Model\BristolStairInfo;
use Bristolian\Parameters\BristolStairsGpsParams;
use Bristolian\Service\BristolStairImageStorage\FakeWorksBristolStairImageStorage;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\BristolStairImageStorage\FakeWorksBristolStairImageStorage::class, 'storeFileForUser')]

class FakeWorksBristolStairImageStorageTest extends BaseTestCase
{
    public function test_storeFileForUser_returns_BristolStairInfo(): void
    {
        $storage = new FakeWorksBristolStairImageStorage();
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/pdfs/sample.pdf');
        $gpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap([
            'gps_latitude' => 51.4545,
            'gps_longitude' => -2.5879,
        ]));

        $result = $storage->storeFileForUser(
            'user_1',
            $uploadedFile,
            ['pdf'],
            $gpsParams
        );

        $this->assertInstanceOf(BristolStairInfo::class, $result);
        $this->assertSame(0, $result->id);
        $this->assertSame('Fake stair info for testing', $result->description);
        $this->assertSame(51.4536491, $result->latitude);
        $this->assertSame(-2.5913353, $result->longitude);
        $this->assertSame(10, $result->steps);
        $this->assertSame(0, $result->is_deleted);
        $this->assertNotEmpty($result->stored_stair_image_file_id);
    }

    public function test_storeFileForUser_increments_id_on_each_call(): void
    {
        $storage = new FakeWorksBristolStairImageStorage();
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/pdfs/sample.pdf');
        $gpsParams = BristolStairsGpsParams::createFromVarMap(new ArrayVarMap([
            'gps_latitude' => 51.4545,
            'gps_longitude' => -2.5879,
        ]));

        $result1 = $storage->storeFileForUser('user_1', $uploadedFile, ['pdf'], $gpsParams);
        $result2 = $storage->storeFileForUser('user_1', $uploadedFile, ['pdf'], $gpsParams);

        $this->assertSame(0, $result1->id);
        $this->assertSame(1, $result2->id);
    }
}
