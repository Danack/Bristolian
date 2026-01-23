<?php

namespace BristolianTest\UserUploadedFile;

use Bristolian\App;
use Bristolian\Session\FakeUserSession;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\JsonResponse;
use SlimDispatcher\Response\StubResponse;

/**
 * @coversNothing
 */
class UserSessionFileUploadHandlerTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\UserUploadedFile\UserSessionFileUploadHandler
     */
    public function testWorks_not_logged_in()
    {
        $userSession = new FakeUserSession(false, '', '');
        $uploadedFiles = new FakeUploadedFiles([]);

        $handler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $result = $handler->fetchUploadedFile('test_file');

        $this->assertInstanceOf(StubResponse::class, $result);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame(400, $result->getStatus());
    }

    /**
     * @covers \Bristolian\UserUploadedFile\UserSessionFileUploadHandler
     */
    public function testWorks_uploaded_file_not_found()
    {
        $userSession = new FakeUserSession(true, 'user123', 'testuser');
        $uploadedFiles = new FakeUploadedFiles([]);

        $handler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $result = $handler->fetchUploadedFile('non_existent_file');

        $this->assertInstanceOf(StubResponse::class, $result);
        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertSame(500, $result->getStatus());
    }

    /**
     * @covers \Bristolian\UserUploadedFile\UserSessionFileUploadHandler
     */
    public function testWorks_file_does_not_exist()
    {
        $userSession = new FakeUserSession(true, 'user123', 'testuser');
        $tmp_name = '/tmp/non_existent_file_' . uniqid() . '.txt';
        $uploadedFile = new UploadedFile($tmp_name, 1024, 'test.txt', 0);
        $uploadedFiles = new FakeUploadedFiles(['test_file' => $uploadedFile]);

        $handler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $result = $handler->fetchUploadedFile('test_file');

        $this->assertInstanceOf(StubResponse::class, $result);
        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertSame(500, $result->getStatus());
    }

    /**
     * @covers \Bristolian\UserUploadedFile\UserSessionFileUploadHandler
     */
    public function testWorks_file_too_large()
    {
        $userSession = new FakeUserSession(true, 'user123', 'testuser');
        $tmp_name = __FILE__; // Use existing file
        $file_size = App::MAX_MEME_FILE_SIZE + 1; // Exceed max size
        $uploadedFile = new UploadedFile($tmp_name, $file_size, 'test.txt', 0);
        $uploadedFiles = new FakeUploadedFiles(['test_file' => $uploadedFile]);

        $handler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $result = $handler->fetchUploadedFile('test_file');

        $this->assertInstanceOf(StubResponse::class, $result);
        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertSame(406, $result->getStatus());
    }

    /**
     * @covers \Bristolian\UserUploadedFile\UserSessionFileUploadHandler
     */
    public function testWorks_success()
    {
        $userSession = new FakeUserSession(true, 'user123', 'testuser');
        $tmp_name = __FILE__; // Use existing file
        $file_size = 1024; // Within max size
        $uploadedFile = new UploadedFile($tmp_name, $file_size, 'test.txt', 0);
        $uploadedFiles = new FakeUploadedFiles(['test_file' => $uploadedFile]);

        $handler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $result = $handler->fetchUploadedFile('test_file');

        $this->assertInstanceOf(UploadedFile::class, $result);
        $this->assertSame($tmp_name, $result->getTmpName());
        $this->assertSame($file_size, $result->getSize());
    }
}
