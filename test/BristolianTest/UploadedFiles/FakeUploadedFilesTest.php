<?php

namespace BristolianTest\UploadedFiles;

use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use Bristolian\UploadedFiles\FakeUploadedFiles;

/**
 * @coversNothing
 */
class FakeUploadedFilesTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\UploadedFiles\FakeUploadedFiles
     */
    public function testWorks()
    {
        $file = new UploadedFile(
            __FILE__,
            \Safe\filesize(__FILE__),
            'test.php',
            0
        );

        $uploadedFiles = new FakeUploadedFiles([
            'this-file' => $file
        ]);

        $result = $uploadedFiles->get('non-existent-file');
        $this->assertNull($result);

        $result = $uploadedFiles->get('this-file');
        $this->assertSame($result, $file);
    }
}
