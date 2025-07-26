<?php

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Service\MemeStorageProcessor\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Service\MemeStorageProcessor\UploadError
 */
class UploadErrorTest extends BaseTestCase
{
    public function testWorks()
    {
        $error1 = UploadError::uploadedFileUnreadable();
        $error2 = UploadError::unsupportedFileType();

        /*$this->assertIsString($error1->error_message);
        $this->assertIsString($error2->error_message);*/
    }
}
