<?php

namespace BristolianTest\UploadedFiles;

use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class UploadedFileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\UploadedFiles\UploadedFile
     */
    public function testWorks()
    {
        $original_name = 'test.php';

        $file = new UploadedFile(
            __FILE__,
            \Safe\filesize(__FILE__),
            $original_name,
            0
        );

        $this->assertSame(__FILE__, $file->getTmpName());
        $this->assertSame($original_name, $file->getOriginalName());
        $this->assertSame(\Safe\filesize(__FILE__), $file->getSize());
        $this->assertSame(0, $file->getError());
    }
}
