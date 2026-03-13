<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomFileStorage;

use Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use Bristolian\Service\RoomFileStorage\FakeRoomFileStorage;
use Bristolian\Service\RoomFileStorage\HandleRoomFileUpload;
use Bristolian\Service\RoomFileStorage\StandardRoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\Session\FakeUserSession;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\StubResponse;

/**
 * @coversNothing
 */
class HandleRoomFileUploadTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\RoomFileStorage\HandleRoomFileUpload::__construct
     * @covers \Bristolian\Service\RoomFileStorage\HandleRoomFileUpload::handle
     */
    public function test_handle_returns_failureResponse_when_upload_handler_returns_stub_response(): void
    {
        $userSession = new FakeUserSession(false, '', '');
        $uploadedFiles = new FakeUploadedFiles([]);
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $storage = new FakeRoomFileStorage('ignored');
        $handler = new HandleRoomFileUpload($storage, $uploadHandler);

        $result = $handler->handle('user_1', 'room_1', 'room_file');

        $this->assertFalse($result->ok);
        $this->assertNull($result->fileId);
        $this->assertNull($result->error);
        $this->assertInstanceOf(StubResponse::class, $result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\HandleRoomFileUpload::handle
     */
    public function test_handle_returns_failure_when_storage_returns_upload_error(): void
    {
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $uploadedFiles = new FakeUploadedFiles(['room_file' => $uploadedFile]);
        $userSession = new FakeUserSession(true, 'user_1', 'testuser');
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $storage = new StandardRoomFileStorage(
            new FakeRoomFileObjectStore(),
            new FakeRoomFileObjectInfoRepo(),
            new FakeRoomFileRepo()
        );
        $handler = new HandleRoomFileUpload($storage, $uploadHandler);

        $result = $handler->handle('user_1', 'room_1', 'room_file');

        $this->assertFalse($result->ok);
        $this->assertNull($result->fileId);
        $this->assertInstanceOf(UploadError::class, $result->error);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error->error_message);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\HandleRoomFileUpload::handle
     */
    public function test_handle_returns_success_when_storage_stores_file(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/images/invalid_avatar.jpg';
        if (!is_readable($imagePath)) {
            $this->markTestSkipped('Fixture not found: ' . $imagePath);
        }
        $uploadedFile = UploadedFile::fromFile($imagePath);
        $uploadedFiles = new FakeUploadedFiles(['room_file' => $uploadedFile]);
        $userSession = new FakeUserSession(true, 'user_1', 'testuser');
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $storage = new StandardRoomFileStorage(
            new FakeRoomFileObjectStore(),
            new FakeRoomFileObjectInfoRepo(),
            new FakeRoomFileRepo()
        );
        $handler = new HandleRoomFileUpload($storage, $uploadHandler);

        $result = $handler->handle('user_1', 'room_1', 'room_file');

        $this->assertTrue($result->ok);
        $this->assertNotNull($result->fileId);
        $this->assertNull($result->error);
        $this->assertNull($result->errorResponse);
    }
}
