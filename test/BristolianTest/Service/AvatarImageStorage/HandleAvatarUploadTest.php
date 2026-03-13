<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use Bristolian\Service\AvatarImageStorage\HandleAvatarUpload;
use Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage;
use Bristolian\Service\AvatarImageStorage\UploadError;
use Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore;
use Bristolian\Session\FakeUserSession;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use BristolianTest\AppController\FakeAvatarImageStorageForUsersTest;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\StubResponse;

/**
 * @coversNothing
 */
class HandleAvatarUploadTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\AvatarImageStorage\HandleAvatarUpload::__construct
     * @covers \Bristolian\Service\AvatarImageStorage\HandleAvatarUpload::handle
     */
    public function test_handle_returns_failureResponse_when_upload_handler_returns_stub_response(): void
    {
        $userSession = new FakeUserSession(false, '', '');
        $uploadedFiles = new FakeUploadedFiles([]);
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $storage = new FakeAvatarImageStorageForUsersTest('ignored');
        $userProfileRepo = new FakeUserProfileRepo();

        $handler = new HandleAvatarUpload($storage, $uploadHandler, $userProfileRepo);

        $result = $handler->handle('user_1', 'avatar');

        $this->assertFalse($result->ok);
        $this->assertNull($result->avatarImageId);
        $this->assertNull($result->error);
        $this->assertInstanceOf(StubResponse::class, $result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\HandleAvatarUpload::handle
     */
    public function test_handle_returns_failure_when_storage_returns_upload_error(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/images/small_avatar.jpg';
        if (!is_readable($imagePath)) {
            $this->markTestSkipped('Fixture not found: ' . $imagePath);
        }
        $uploadedFile = UploadedFile::fromFile($imagePath);
        $uploadedFiles = new FakeUploadedFiles(['avatar' => $uploadedFile]);
        $userSession = new FakeUserSession(true, 'user_1', 'testuser');
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $storage = new StandardAvatarImageStorage(
            new FakeAvatarImageStorageInfoRepo(),
            new FakeAvatarImageObjectStore()
        );
        $userProfileRepo = new FakeUserProfileRepo();

        $handler = new HandleAvatarUpload($storage, $uploadHandler, $userProfileRepo);

        $result = $handler->handle('user_1', 'avatar');

        $this->assertFalse($result->ok);
        $this->assertNull($result->avatarImageId);
        $this->assertInstanceOf(UploadError::class, $result->error);
        $this->assertNull($result->errorResponse);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\HandleAvatarUpload::handle
     */
    public function test_handle_returns_success_and_updates_profile_when_storage_succeeds(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/stairs/stairs_test_c_7.jpeg';
        if (!is_readable($imagePath)) {
            $this->markTestSkipped('Fixture not found: ' . $imagePath);
        }
        $imageSize = @getimagesize($imagePath);
        $minSize = 512;
        if ($imageSize === false || $imageSize[0] < $minSize || $imageSize[1] < $minSize) {
            $this->markTestSkipped("Fixture image must be at least {$minSize}x{$minSize} for this test");
        }

        $uploadedFile = UploadedFile::fromFile($imagePath);
        $uploadedFiles = new FakeUploadedFiles(['avatar' => $uploadedFile]);
        $userSession = new FakeUserSession(true, 'user_1', 'testuser');
        $uploadHandler = new UserSessionFileUploadHandler($userSession, $uploadedFiles);

        $storage = new StandardAvatarImageStorage(
            new FakeAvatarImageStorageInfoRepo(),
            new FakeAvatarImageObjectStore()
        );
        $userProfileRepo = new FakeUserProfileRepo();

        $handler = new HandleAvatarUpload($storage, $uploadHandler, $userProfileRepo);

        $result = $handler->handle('user_1', 'avatar');

        $this->assertTrue($result->ok);
        $this->assertNotNull($result->avatarImageId);
        $this->assertNull($result->error);
        $this->assertNull($result->errorResponse);

        $profile = $userProfileRepo->getUserProfile('user_1');
        $this->assertSame($result->avatarImageId, $profile->getAvatarImageId());
    }
}
