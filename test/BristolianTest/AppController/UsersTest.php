<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Asm\Encrypter\NullEncrypterFactory;
use Asm\SessionConfig;
use Asm\SessionManager;
use Bristolian\AppController\Users;
use Bristolian\Parameters\UserProfileUpdateParams;
use Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo;
use Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo;
use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;
use Bristolian\Response\GetUserInfoResponse;
use Bristolian\Response\UpdateUserProfileResponse;
use Bristolian\Response\UploadAvatarErrorResponse;
use Bristolian\Response\UploadAvatarResponse;
use Bristolian\Session\AppSession;
use Bristolian\Session\AppSessionManager;
use Bristolian\Session\FakeAsmDriver;
use Bristolian\Service\AvatarImageStorage\AvatarImageStorage;
use Bristolian\Service\AvatarImageStorage\UploadError;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFiles;
use Bristolian\UserUploadedFile\UserSessionFileUploadHandler;
use BristolianTest\BaseTestCase;
use BristolianTest\Session\FakeAsmSession;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Laminas\Diactoros\ServerRequest;
use SlimDispatcher\Response\HtmlResponse;
use SlimDispatcher\Response\JsonResponse;
use VarMap\ArrayVarMap;

/**
 * Fake AvatarImageStorage for UsersTest uploadAvatar tests.
 * @coversNothing
 */
final class FakeAvatarImageStorageForUsersTest implements AvatarImageStorage
{
    public function __construct(
        private string|UploadError $storeResult
    ) {
    }

    public function storeAvatarForUser(string $user_id, \Bristolian\UploadedFiles\UploadedFile $uploadedFile, array $allowedExtensions): string|UploadError
    {
        return $this->storeResult;
    }
}

/**
 * @coversNothing
 */
class UsersTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->injector->alias(UserProfileRepo::class, FakeUserProfileRepo::class);
        $this->injector->share(FakeUserProfileRepo::class);
        $this->setupFakeUserSession();
    }

    private function createSessionManager(FakeAsmDriver $driver = null): SessionManager
    {
        $config = new SessionConfig('test_session', 3600);
        $driver = $driver ?? new FakeAsmDriver([
            ['Set-Cookie', 'test_session=abc; path=/; httpOnly'],
        ]);
        return new SessionManager($config, $driver, null, new NullEncrypterFactory());
    }

    /**
     * @param array<string, string> $cookies
     */
    private function createRequest(array $cookies = []): ServerRequest
    {
        return (new ServerRequest())->withCookieParams($cookies);
    }

    /**
     * @covers \Bristolian\AppController\Users::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Users::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('User list', $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::whoami
     */
    public function test_whoami_not_logged_in(): void
    {
        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);

        $result = $this->injector->execute([Users::class, 'whoami']);

        $this->assertInstanceOf(HtmlResponse::class, $result);
        $this->assertSame(404, $result->getStatus());
        $body = $result->getBody();
        $this->assertStringContainsString('Not logged in', $body);
    }

    /**
     * @covers \Bristolian\AppController\Users::whoami
     */
    public function test_whoami_logged_in(): void
    {
        $existingSession = new FakeAsmSession('sess-whoami');
        $existingSession->set(AppSession::LOGGED_IN, true);
        $existingSession->set(AppSession::USER_ID, 'test-user-id-001');
        $existingSession->set(AppSession::USERNAME, 'testuser@example.com');
        $driver = new FakeAsmDriver();
        $driver->addSession($existingSession);
        $sessionManager = $this->createSessionManager($driver);
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest(['test_session' => 'sess-whoami']));
        $this->injector->share($appSessionManager);

        $result = $this->injector->execute([Users::class, 'whoami']);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $rawBody = $result->getBody();
        $body = json_decode($rawBody, true);
        $this->assertSame('test-user-id-001', $body['user_id']);
    }

    /**
     * @covers \Bristolian\AppController\Users::getUserInfo
     */
    public function test_getUserInfo(): void
    {
        $this->injector->defineParam('user_id', 'test-user-id-001');
        $result = $this->injector->execute([Users::class, 'getUserInfo']);
        $this->assertInstanceOf(GetUserInfoResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::showOwnProfile
     */
    public function test_showOwnProfile(): void
    {
        $existingSession = new FakeAsmSession('sess-own');
        $existingSession->set(AppSession::LOGGED_IN, true);
        $existingSession->set(AppSession::USER_ID, 'test-user-id-001');
        $existingSession->set(AppSession::USERNAME, 'testuser@example.com');
        $driver = new FakeAsmDriver();
        $driver->addSession($existingSession);
        $sessionManager = $this->createSessionManager($driver);
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest(['test_session' => 'sess-own']));
        $this->injector->share($appSessionManager);

        $result = $this->injector->execute([Users::class, 'showOwnProfile']);

        $this->assertIsString($result);
        $this->assertStringContainsString('User Profile', $result);
        $this->assertStringContainsString('user_profile_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::showUserProfile
     */
    public function test_showUserProfile_different_user(): void
    {
        $sessionManager = $this->createSessionManager();
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest());
        $this->injector->share($appSessionManager);
        $this->injector->defineParam('user_id', 'other-user-id');

        $result = $this->injector->execute([Users::class, 'showUserProfile']);

        $this->assertIsString($result);
        $this->assertStringContainsString('User Profile', $result);
        $this->assertStringContainsString('is_own_profile', $result);
        $this->assertStringContainsString('false', $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::showUserProfile
     */
    public function test_showUserProfile_same_user(): void
    {
        $existingSession = new FakeAsmSession('sess-same');
        $existingSession->set(AppSession::LOGGED_IN, true);
        $existingSession->set(AppSession::USER_ID, 'test-user-id-001');
        $existingSession->set(AppSession::USERNAME, 'testuser@example.com');
        $driver = new FakeAsmDriver();
        $driver->addSession($existingSession);
        $sessionManager = $this->createSessionManager($driver);
        $appSessionManager = new AppSessionManager($sessionManager);
        $appSessionManager->initialize($this->createRequest(['test_session' => 'sess-same']));
        $this->injector->share($appSessionManager);
        $this->injector->defineParam('user_id', 'test-user-id-001');

        $result = $this->injector->execute([Users::class, 'showUserProfile']);

        $this->assertIsString($result);
        $this->assertStringContainsString('User Profile', $result);
        $this->assertStringContainsString('is_own_profile', $result);
        $this->assertStringContainsString('true', $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::getUserAvatar
     */
    public function test_getUserAvatar_no_avatar_returns_error(): void
    {
        $tempRoot = sys_get_temp_dir() . '/bristolian_avatar_test_' . uniqid();
        mkdir($tempRoot, 0755, true);
        $adapter = new LocalFilesystemAdapter($tempRoot);
        $avatarFs = new \Bristolian\Filesystem\AvatarImageFilesystem($adapter, []);
        $cacheFs = new \Bristolian\Filesystem\LocalCacheFilesystem($adapter, $tempRoot);
        $this->injector->share($avatarFs);
        $this->injector->share($cacheFs);
        $this->injector->alias(AvatarImageStorageInfoRepo::class, FakeAvatarImageStorageInfoRepo::class);
        $this->injector->share(FakeAvatarImageStorageInfoRepo::class);
        $this->injector->defineParam('user_id', 'test-user-id-001');

        $result = $this->injector->execute([Users::class, 'getUserAvatar']);

        $this->assertInstanceOf(\Bristolian\Response\StoredFileErrorResponse::class, $result);
        $responseBody = $result->getBody();
        $this->assertStringContainsString('No avatar for user', $responseBody);
    }

    /**
     * @covers \Bristolian\AppController\Users::getUserAvatar
     */
    public function test_getUserAvatar_has_avatar_returns_streaming_response(): void
    {
        $tempRoot = sys_get_temp_dir() . '/bristolian_avatar_test_' . uniqid();
        mkdir($tempRoot, 0755, true);
        $normalizedName = 'user-avatar-' . uniqid() . '.jpg';
        $tempFilePath = $tempRoot . '/' . $normalizedName;
        file_put_contents($tempFilePath, 'image content');

        $adapter = new LocalFilesystemAdapter($tempRoot);
        $avatarFs = new \Bristolian\Filesystem\AvatarImageFilesystem($adapter, []);
        $cacheFs = new \Bristolian\Filesystem\LocalCacheFilesystem($adapter, $tempRoot);
        $this->injector->share($avatarFs);
        $this->injector->share($cacheFs);

        $uploadedFile = new \Bristolian\UploadedFiles\UploadedFile($tempFilePath, 14, 'avatar.jpg', 0);
        $storageRepo = new FakeAvatarImageStorageInfoRepo();
        $avatarImageId = $storageRepo->storeFileInfo('test-user-id-001', $normalizedName, $uploadedFile);
        $this->injector->alias(AvatarImageStorageInfoRepo::class, FakeAvatarImageStorageInfoRepo::class);
        $this->injector->share($storageRepo);

        $userProfileRepo = $this->injector->make(FakeUserProfileRepo::class);
        $userProfileRepo->updateAvatarImage('test-user-id-001', $avatarImageId);
        $this->injector->defineParam('user_id', 'test-user-id-001');

        $result = $this->injector->execute([Users::class, 'getUserAvatar']);

        $this->assertInstanceOf(\Bristolian\Response\StreamingResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::getAvatarImage
     */
    public function test_getAvatarImage_not_found_returns_error(): void
    {
        $tempRoot = sys_get_temp_dir() . '/bristolian_avatar_test_' . uniqid();
        mkdir($tempRoot, 0755, true);
        $adapter = new LocalFilesystemAdapter($tempRoot);
        $avatarFs = new \Bristolian\Filesystem\AvatarImageFilesystem($adapter, []);
        $cacheFs = new \Bristolian\Filesystem\LocalCacheFilesystem($adapter, $tempRoot);
        $this->injector->share($avatarFs);
        $this->injector->share($cacheFs);
        $this->injector->alias(AvatarImageStorageInfoRepo::class, FakeAvatarImageStorageInfoRepo::class);
        $this->injector->share(FakeAvatarImageStorageInfoRepo::class);
        $this->injector->defineParam('avatar_image_id', 'nonexistent-avatar-id');

        $result = $this->injector->execute([Users::class, 'getAvatarImage']);

        $this->assertInstanceOf(\Bristolian\Response\StoredFileErrorResponse::class, $result);
        $responseBody = $result->getBody();
        $this->assertStringContainsString('nonexistent-avatar-id', $responseBody);
    }

    /**
     * @covers \Bristolian\AppController\Users::getAvatarImage
     */
    public function test_getAvatarImage_found_returns_streaming_response(): void
    {
        $tempRoot = sys_get_temp_dir() . '/bristolian_avatar_test_' . uniqid();
        mkdir($tempRoot, 0755, true);
        $normalizedName = 'avatar-cached-' . uniqid() . '.jpg';
        $tempFilePath = $tempRoot . '/' . $normalizedName;
        file_put_contents($tempFilePath, 'image content');

        $adapter = new LocalFilesystemAdapter($tempRoot);
        $avatarFs = new \Bristolian\Filesystem\AvatarImageFilesystem($adapter, []);
        $cacheFs = new \Bristolian\Filesystem\LocalCacheFilesystem($adapter, $tempRoot);
        $this->injector->share($avatarFs);
        $this->injector->share($cacheFs);

        $uploadedFile = new \Bristolian\UploadedFiles\UploadedFile($tempFilePath, 14, 'avatar.jpg', 0);
        $repo = new FakeAvatarImageStorageInfoRepo();
        $avatarImageId = $repo->storeFileInfo('test-user', $normalizedName, $uploadedFile);
        $this->injector->alias(AvatarImageStorageInfoRepo::class, FakeAvatarImageStorageInfoRepo::class);
        $this->injector->share($repo);
        $this->injector->defineParam('avatar_image_id', $avatarImageId);

        $result = $this->injector->execute([Users::class, 'getAvatarImage']);

        $this->assertInstanceOf(\Bristolian\Response\StreamingResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::uploadAvatar
     */
    public function test_uploadAvatar_no_file_returns_stub_response(): void
    {
        $uploadedFiles = new FakeUploadedFiles([]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $uploadHandler = new UserSessionFileUploadHandler($this->injector->make(\Bristolian\Session\UserSession::class), $uploadedFiles);
        $this->injector->share($uploadHandler);
        $storage = new FakeAvatarImageStorageForUsersTest('avatar-id-123');
        $this->injector->alias(AvatarImageStorage::class, FakeAvatarImageStorageForUsersTest::class);
        $this->injector->share($storage);

        $result = $this->injector->execute([Users::class, 'uploadAvatar']);

        $this->assertInstanceOf(\SlimDispatcher\Response\StubResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::uploadAvatar
     */
    public function test_uploadAvatar_storage_returns_error_returns_400(): void
    {
        $uploadedFile = new \Bristolian\UploadedFiles\UploadedFile(__FILE__, 1024, 'test.php', 0);
        $uploadedFiles = new FakeUploadedFiles(['avatar_file' => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $uploadHandler = new UserSessionFileUploadHandler($this->injector->make(\Bristolian\Session\UserSession::class), $uploadedFiles);
        $this->injector->share($uploadHandler);
        $storage = new FakeAvatarImageStorageForUsersTest(UploadError::uploadedFileUnreadable());
        $this->injector->alias(AvatarImageStorage::class, FakeAvatarImageStorageForUsersTest::class);
        $this->injector->share($storage);

        $result = $this->injector->execute([Users::class, 'uploadAvatar']);

        $this->assertInstanceOf(UploadAvatarErrorResponse::class, $result);
        $this->assertSame(400, $result->getStatus());
    }

    /**
     * @covers \Bristolian\AppController\Users::uploadAvatar
     */
    public function test_uploadAvatar_success_returns_upload_avatar_response(): void
    {
        $uploadedFile = new \Bristolian\UploadedFiles\UploadedFile(__FILE__, 1024, 'test.php', 0);
        $uploadedFiles = new FakeUploadedFiles(['avatar_file' => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $uploadHandler = new UserSessionFileUploadHandler($this->injector->make(\Bristolian\Session\UserSession::class), $uploadedFiles);
        $this->injector->share($uploadHandler);
        $storage = new FakeAvatarImageStorageForUsersTest('new-avatar-id-456');
        $this->injector->alias(AvatarImageStorage::class, FakeAvatarImageStorageForUsersTest::class);
        $this->injector->share($storage);

        $result = $this->injector->execute([Users::class, 'uploadAvatar']);

        $this->assertInstanceOf(UploadAvatarResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Users::updateProfile
     */
    public function test_updateProfile(): void
    {
        $params = UserProfileUpdateParams::createFromVarMap(new ArrayVarMap([
            'display_name' => 'TestUser',
            'about_me' => 'About me text here.',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([Users::class, 'updateProfile']);
        $this->assertInstanceOf(UpdateUserProfileResponse::class, $result);
    }
}
