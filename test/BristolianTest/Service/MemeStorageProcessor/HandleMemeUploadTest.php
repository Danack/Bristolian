<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Service\MemeStorageProcessor\HandleMemeUpload;
use Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\FakeMemeObjectStore;
use Bristolian\Session\FakeUserSession;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\StubResponse;

/**
 * @coversNothing
 */
class HandleMemeUploadTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\HandleMemeUpload::__construct
     * @covers \Bristolian\Service\MemeStorageProcessor\HandleMemeUpload::handle
     */
    public function test_handle_returns_failureResponse_when_upload_handler_returns_stub_response(): void
    {
        $userSession = new FakeUserSession(false, '', '');
        $uploadedFiles = new FakeUploadedFiles([]);
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $processor = new \Bristolian\Service\MemeStorageProcessor\FakeWorksMemeStorageProcessor();
        $memeObjectStore = new FakeMemeObjectStore();

        $handler = new HandleMemeUpload($processor, $uploadHandler, $memeObjectStore);

        $result = $handler->handle('user_1', 'meme_file');

        $this->assertFalse($result->ok);
        $this->assertNull($result->meme);
        $this->assertNull($result->error);
        $this->assertInstanceOf(StubResponse::class, $result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\HandleMemeUpload::handle
     */
    public function test_handle_returns_failure_when_processor_returns_upload_error(): void
    {
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $uploadedFiles = new FakeUploadedFiles(['meme_file' => $uploadedFile]);
        $userSession = new FakeUserSession(true, 'user_1', 'testuser');
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $processor = new StandardMemeStorageProcessor(new FakeMemeStorageRepo());
        $memeObjectStore = new FakeMemeObjectStore();

        $handler = new HandleMemeUpload($processor, $uploadHandler, $memeObjectStore);

        $result = $handler->handle('user_1', 'meme_file');

        $this->assertFalse($result->ok);
        $this->assertNull($result->meme);
        $this->assertInstanceOf(UploadError::class, $result->error);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error->error_message);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\HandleMemeUpload::handle
     */
    public function test_handle_returns_success_when_processor_stores_meme(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/stairs/stairs_test_c_7.jpeg';
        if (!is_readable($imagePath)) {
            $this->markTestSkipped('Fixture image not found: ' . $imagePath);
        }
        $uploadedFile = UploadedFile::fromFile($imagePath);
        $uploadedFiles = new FakeUploadedFiles(['meme_file' => $uploadedFile]);
        $userSession = new FakeUserSession(true, 'user_1', 'testuser');
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $processor = new StandardMemeStorageProcessor(new FakeMemeStorageRepo());
        $memeObjectStore = new FakeMemeObjectStore();

        $handler = new HandleMemeUpload($processor, $uploadHandler, $memeObjectStore);

        $result = $handler->handle('user_1', 'meme_file');

        $this->assertTrue($result->ok);
        $this->assertNotNull($result->meme);
        $this->assertNull($result->error);
        $this->assertNull($result->errorResponse);
        $this->assertMatchesRegularExpression('/\.(jpeg|jpg)$/', $result->meme->normalized_filename);
        $this->assertTrue($memeObjectStore->hasFile($result->meme->normalized_filename));
    }
}
