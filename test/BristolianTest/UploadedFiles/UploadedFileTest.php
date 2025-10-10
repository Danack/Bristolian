<?php

namespace BristolianTest\UploadedFiles;

use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 * @group wip
 */
class UploadedFileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\UploadedFiles\UploadedFile
     */
    public function testWorks()
    {
        $original_name = 'test.php';

        // normal constructor
        $file1 = new UploadedFile(
            __FILE__,
            \Safe\filesize(__FILE__),
            $original_name,
            0
        );
        $this->assertSame(__FILE__, $file1->getTmpName());
        $this->assertSame($original_name, $file1->getOriginalName());
        $this->assertSame(\Safe\filesize(__FILE__), $file1->getSize());
        $this->assertSame(0, $file1->getErrorCode());


        $error_message = $file1->getErrorMessage();
        $this->assertSame(
            'There is no error, the file uploaded with success',
            $error_message
        );





        // static constructor
        $file2 = UploadedFile::fromFile(__FILE__);
        $this->assertSame(__FILE__, $file2->getTmpName());
        $this->assertSame(__FILE__, $file2->getOriginalName());
        $this->assertSame(\Safe\filesize(__FILE__), $file2->getSize());
        $this->assertSame(0, $file2->getErrorCode());

    }
}
