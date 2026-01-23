<?php

namespace BristolianTest\UserUploadedFile;

use Bristolian\UserUploadedFile\UserUploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UserUploadedFileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\UserUploadedFile\UserUploadedFile
     */
    public function testWorks()
    {
        $tmp_name = '/tmp/test_file.txt';
        $size = 1024;
        $name = 'test_file.txt';

        $file = new UserUploadedFile($tmp_name, $size, $name);

        $this->assertSame($tmp_name, $file->getTmpName());
        $this->assertSame($size, $file->getSize());
        $this->assertSame($name, $file->getName());
    }
}
