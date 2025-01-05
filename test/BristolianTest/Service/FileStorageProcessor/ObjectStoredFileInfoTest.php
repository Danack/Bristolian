<?php

namespace BristolianTest\Service\FileStorageProcessor;

use BristolianTest\BaseTestCase;
use Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo;

/**
 * @covers \Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo
 */
class ObjectStoredFileInfoTest extends BaseTestCase
{
    public function testWorks()
    {
        $normalized_filename = "foo";
        $fileStorageId = '12345';

        $object = new ObjectStoredFileInfo($normalized_filename, $fileStorageId);
        $this->assertSame($normalized_filename, $object->normalized_filename);
        $this->assertSame($fileStorageId, $object->fileStorageId);
    }
}
