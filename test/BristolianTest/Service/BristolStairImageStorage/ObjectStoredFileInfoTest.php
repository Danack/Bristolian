<?php

declare(strict_types=1);

namespace BristolianTest\Service\BristolStairImageStorage;

use Bristolian\Service\BristolStairImageStorage\ObjectStoredFileInfo;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\BristolStairImageStorage\ObjectStoredFileInfo::class, '__construct')]

class ObjectStoredFileInfoTest extends BaseTestCase
{
    public function test_construct_stores_normalized_filename_and_file_storage_id(): void
    {
        $normalizedFilename = 'uuid-123.jpg';
        $fileStorageId = 'fs_456';
        $info = new ObjectStoredFileInfo($normalizedFilename, $fileStorageId);
        $this->assertSame($normalizedFilename, $info->normalized_filename);
        $this->assertSame($fileStorageId, $info->fileStorageId);
    }
}
