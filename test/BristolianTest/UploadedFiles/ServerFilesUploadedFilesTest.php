<?php

namespace BristolianTest\UploadedFiles;

use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use BristolianTest\BaseTestCase;
use Bristolian\UploadedFiles\ServerFilesUploadedFiles;

/**
 * @covers \Bristolian\UploadedFiles\ServerFilesUploadedFiles
 */
class ServerFilesUploadedFilesTest extends BaseTestCase
{
    private array $originalFiles;

    public function setUp(): void
    {
        parent::setUp();
        // Save the original $_FILES array
        $this->originalFiles = $_FILES;
        // Clear $_FILES for each test
        $_FILES = [];
    }

    public function tearDown(): void
    {
        // Restore the original $_FILES array
        $_FILES = $this->originalFiles;
        parent::tearDown();
    }

    public function testWorksWithValidUpload()
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
        $this->assertSame(0, $uploaded_file->getErrorCode());
    }

    public function testReturnsNullForNonExistentFile()
    {
        $sfuf = new ServerFilesUploadedFiles();

        $result = $sfuf->get('non-existent-file');
        
        $this->assertNull($result);
    }

    public function testImplementsUploadedFilesInterface()
    {
        $sfuf = new ServerFilesUploadedFiles();
        
        $this->assertInstanceOf(UploadedFiles::class, $sfuf);
    }

    public function testReturnsNullWhenMissingTmpNameKey()
    {
        $file_entry = [
            "size" => 1234,
            "name" => "test.txt",
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $result = $sfuf->get('test_file');
        
        $this->assertNull($result);
    }

    public function testReturnsNullWhenMissingSizeKey()
    {
        $file_entry = [
            "tmp_name" => "/tmp/phptest",
            "name" => "test.txt",
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $result = $sfuf->get('test_file');
        
        $this->assertNull($result);
    }

    public function testReturnsNullWhenMissingNameKey()
    {
        $file_entry = [
            "tmp_name" => "/tmp/phptest",
            "size" => 1234,
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $result = $sfuf->get('test_file');
        
        $this->assertNull($result);
    }

    public function testReturnsNullWhenMissingErrorKey()
    {
        $file_entry = [
            "tmp_name" => "/tmp/phptest",
            "size" => 1234,
            "name" => "test.txt"
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $result = $sfuf->get('test_file');
        
        $this->assertNull($result);
    }

    public function testWorksWithUploadError()
    {
        $file_entry = [
            "tmp_name" => "",
            "size" => 0,
            "name" => "test.txt",
            "error" => 1  // UPLOAD_ERR_INI_SIZE
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame(1, $uploaded_file->getErrorCode());
    }

    public function testWorksWithEmptyTmpName()
    {
        $file_entry = [
            "tmp_name" => "",
            "size" => 0,
            "name" => "test.txt",
            "error" => 4  // UPLOAD_ERR_NO_FILE
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame("", $uploaded_file->getTmpName());
        $this->assertSame(4, $uploaded_file->getErrorCode());
    }

    public function testWorksWithZeroSize()
    {
        $file_entry = [
            "tmp_name" => "/tmp/phptest",
            "size" => 0,
            "name" => "empty.txt",
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame(0, $uploaded_file->getSize());
    }

    public function testWorksWithSpecialCharactersInFilename()
    {
        $original_name = 'test file with spaces & special (chars).txt';
        
        $file_entry = [
            "tmp_name" => __FILE__,
            "size" => \Safe\filesize(__FILE__),
            "name" => $original_name,
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame($original_name, $uploaded_file->getOriginalName());
    }

    public function testWorksWithUnicodeFilename()
    {
        $original_name = 'тест_файл_中文_🎉.txt';
        
        $file_entry = [
            "tmp_name" => __FILE__,
            "size" => \Safe\filesize(__FILE__),
            "name" => $original_name,
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame($original_name, $uploaded_file->getOriginalName());
    }

    public function testWorksWithLargeFileSize()
    {
        $large_size = 1073741824; // 1GB
        
        $file_entry = [
            "tmp_name" => "/tmp/phptest",
            "size" => $large_size,
            "name" => "large_file.bin",
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame($large_size, $uploaded_file->getSize());
    }

    public function testWorksWithMultipleFiles()
    {
        $_FILES['file1'] = [
            "tmp_name" => __FILE__,
            "size" => \Safe\filesize(__FILE__),
            "name" => "file1.txt",
            "error" => 0
        ];
        
        $_FILES['file2'] = [
            "tmp_name" => __FILE__,
            "size" => \Safe\filesize(__FILE__),
            "name" => "file2.txt",
            "error" => 0
        ];
        
        $sfuf = new ServerFilesUploadedFiles();
        
        $file1 = $sfuf->get('file1');
        $file2 = $sfuf->get('file2');
        
        $this->assertInstanceOf(UploadedFile::class, $file1);
        $this->assertInstanceOf(UploadedFile::class, $file2);
        $this->assertSame('file1.txt', $file1->getOriginalName());
        $this->assertSame('file2.txt', $file2->getOriginalName());
    }

    public function testWorksWithDifferentFormNames()
    {
        $form_names = ['upload', 'file_upload', 'document', 'image_file'];
        
        foreach ($form_names as $form_name) {
            $_FILES[$form_name] = [
                "tmp_name" => __FILE__,
                "size" => \Safe\filesize(__FILE__),
                "name" => "test.txt",
                "error" => 0
            ];
        }
        
        $sfuf = new ServerFilesUploadedFiles();
        
        foreach ($form_names as $form_name) {
            $uploaded_file = $sfuf->get($form_name);
            $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        }
    }

    public function testReturnsNullWhenFilesArrayIsEmpty()
    {
        $_FILES = [];
        
        $sfuf = new ServerFilesUploadedFiles();
        $result = $sfuf->get('any_name');
        
        $this->assertNull($result);
    }

    public function testWorksWithLongFilename()
    {
        $long_name = str_repeat('a', 255) . '.txt';
        
        $file_entry = [
            "tmp_name" => __FILE__,
            "size" => \Safe\filesize(__FILE__),
            "name" => $long_name,
            "error" => 0
        ];
        
        $_FILES['test_file'] = $file_entry;
        
        $sfuf = new ServerFilesUploadedFiles();
        $uploaded_file = $sfuf->get('test_file');
        
        $this->assertInstanceOf(UploadedFile::class, $uploaded_file);
        $this->assertSame($long_name, $uploaded_file->getOriginalName());
    }
}
