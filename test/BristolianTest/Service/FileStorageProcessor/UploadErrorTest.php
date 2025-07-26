<?php

namespace BristolianTest\Service\FileStorageProcessor;

use Bristolian\Service\FileStorageProcessor\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Service\FileStorageProcessor\UploadError
 */
class UploadErrorTest extends BaseTestCase
{
    public function testWorks()
    {
        $error1 = UploadError::uploadedFileUnreadable();
        $error2 = UploadError::unsupportedFileType();

//        $this->assertIsString($error1->error_message);
//        $this->assertIsString($error2->error_message);
    }
}
