<?php

namespace BristolianTest\UploadedFiles;

use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use Bristolian\UploadedFiles\ServerFilesUploadedFiles;

/**
 * @coversNothing
 */
class ServerFilesUploadedFilesTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\UploadedFiles\ServerFilesUploadedFiles
     */
    public function testWorks()
    {
        $original_name = 'test.php';

        $file_entry["tmp_name"] = __FILE__;
        $file_entry["size"] = \Safe\filesize(__FILE__);
        $file_entry["name"] = $original_name;
        $file_entry["error"] = 0;
        $form_name = 'test_file';

        $_FILES[$form_name] = $file_entry;

        $sfuf = new ServerFilesUploadedFiles();

        $uploaded_file = $sfuf->get($form_name);
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);

        $this->assertSame(__FILE__, $uploaded_file->getTmpName());
        $this->assertSame($original_name, $uploaded_file->getOriginalName());
        $this->assertSame(\Safe\filesize(__FILE__), $uploaded_file->getSize());
        $this->assertSame(0, $uploaded_file->getError());

        $this->assertNull($sfuf->get('non-existent-file'));
    }
}
