<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Bristolian\AppController\BristolStairs;
use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Parameters\BristolStairsInfoParams;
use Bristolian\Parameters\BristolStairsPositionParams;
use Bristolian\Parameters\OpenmapNearbyParams;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\BristolStairImageStorageInfoRepo;
use Bristolian\Repo\BristolStairImageStorageInfoRepo\FakeBristolStairImageStorageInfoRepo;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Response\StreamingResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetBristolStairsResponse;
use Bristolian\Response\UploadBristolStairsImageResponse;
use Bristolian\Service\BristolStairImageStorage\BristolStairImageStorage;
use Bristolian\Service\BristolStairImageStorage\UploadError;
use Bristolian\Session\FakeUserSession;
use Bristolian\Session\UserSession;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;
use SlimDispatcher\Response\JsonNoCacheResponse;
use SlimDispatcher\Response\StubResponse;
use VarMap\ArrayVarMap;
use VarMap\VarMap;

/**
 * BristolStairImageStorage that always returns UploadError for testing error path.
 *
 * @coversNothing
 */
final class BristolStairImageStorageReturningUploadError implements BristolStairImageStorage
{
    public function storeFileForUser(
        string $user_id,
        \Bristolian\UploadedFiles\UploadedFile $uploadedFile,
        array $allowedExtensions,
        \Bristolian\Parameters\BristolStairsGpsParams $gpsParams
    ): UploadError {
        return UploadError::unsupportedFileType();
    }
}

/**
 * @coversNothing
 */
class BristolStairsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::update_stairs_info_get
     */
    public function test_update_stairs_info_get(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_info_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::stairs_page
     * @covers \Bristolian\AppController\BristolStairs::render_stairs_page
     */
    public function test_stairs_page(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'stairs_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('A map of Bristol Stairs', $result);
        $this->assertStringContainsString('bristol_stairs_map', $result);
        $this->assertStringContainsString('flights of stairs', $result);
        $this->assertStringContainsString('steps', $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::stairs_page_stair_selected
     * @covers \Bristolian\AppController\BristolStairs::render_stairs_page
     */
    public function test_stairs_page_stair_selected(): void
    {
        $this->injector->defineParam('stair_id', 1);
        $result = $this->injector->execute([BristolStairs::class, 'stairs_page_stair_selected']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Steep stairs near Park Street', $result);
        $this->assertStringContainsString('flights of stairs', $result);
        $this->assertStringContainsString('steps', $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getData
     */
    public function test_getData(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'getData']);
        $this->assertInstanceOf(GetBristolStairsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::update_stairs_info
     */
    public function test_update_stairs_info(): void
    {
        $this->setupFakeUserSession();
        $params = BristolStairsInfoParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'description' => 'Updated description',
            'steps' => '50',
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_info']);

        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::update_stairs_position
     */
    public function test_update_stairs_position(): void
    {
        $this->setupFakeUserSession();
        $params = BristolStairsPositionParams::createFromVarMap(new ArrayVarMap([
            'bristol_stair_info_id' => '1',
            'latitude' => 51.46,
            'longitude' => -2.60,
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_position']);

        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getOpenmapNearby
     */
    public function test_getOpenmapNearby_returns_error_when_not_logged_in(): void
    {
        $session = new FakeUserSession(false, '', '');
        $this->injector->alias(UserSession::class, FakeUserSession::class);
        $this->injector->share($session);
        $params = OpenmapNearbyParams::createFromVarMap(new ArrayVarMap([
            'latitude' => 51.45,
            'longitude' => -2.59,
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'getOpenmapNearby']);

        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertStringContainsString('Not logged in', $result->getBody());
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getOpenmapNearby
     */
    public function test_getOpenmapNearby_returns_locations_when_logged_in(): void
    {
        $this->setupFakeUserSession();
        $params = OpenmapNearbyParams::createFromVarMap(new ArrayVarMap([
            'latitude' => 51.45,
            'longitude' => -2.59,
        ]));
        $this->injector->share($params);

        $result = $this->injector->execute([BristolStairs::class, 'getOpenmapNearby']);

        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('success', $body['result'] ?? null);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('locations', $body['data']);
        $this->assertIsArray($body['data']['locations']);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getImage
     */
    public function test_getImage_returns_StreamingResponse_when_file_exists(): void
    {
        $tempRoot = sys_get_temp_dir() . '/bristol_stairs_image_' . uniqid();
        mkdir($tempRoot, 0755, true);
        $normalizedName = 'stair-' . uniqid() . '.jpg';
        $filePath = $tempRoot . '/' . $normalizedName;
        file_put_contents($filePath, 'image content');

        $adapter = new LocalFilesystemAdapter($tempRoot);
        $stairsFilesystem = new BristolStairsFilesystem($adapter, []);
        $localCacheFilesystem = new LocalCacheFilesystem($adapter, $tempRoot);
        $this->injector->share($stairsFilesystem);
        $this->injector->share($localCacheFilesystem);

        $uploadedFile = new UploadedFile($filePath, 13, 'stair.jpg', 0);
        $repo = new FakeBristolStairImageStorageInfoRepo();
        $fileId = $repo->storeFileInfo('user-1', $normalizedName, $uploadedFile);
        $this->injector->alias(BristolStairImageStorageInfoRepo::class, FakeBristolStairImageStorageInfoRepo::class);
        $this->injector->share($repo);

        $this->injector->defineParam('stored_stair_image_file_id', $fileId);

        $result = $this->injector->execute([BristolStairs::class, 'getImage']);

        $this->assertInstanceOf(StreamingResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::getImage
     */
    public function test_getImage_returns_StoredFileErrorResponse_when_file_unreadable(): void
    {
        $tempRoot = sys_get_temp_dir() . '/bristol_stairs_image_' . uniqid();
        mkdir($tempRoot, 0755, true);
        $normalizedName = 'missing-' . uniqid() . '.jpg';

        $adapter = new LocalFilesystemAdapter($tempRoot);
        $stairsFilesystem = new BristolStairsFilesystem($adapter, []);
        $localCacheFilesystem = new LocalCacheFilesystem($adapter, $tempRoot);
        $this->injector->share($stairsFilesystem);
        $this->injector->share($localCacheFilesystem);

        $dummyPath = $tempRoot . '/dummy.jpg';
        file_put_contents($dummyPath, 'x');
        $uploadedFile = new UploadedFile($dummyPath, 1, 'dummy.jpg', 0);
        $repo = new FakeBristolStairImageStorageInfoRepo();
        $fileId = $repo->storeFileInfo('user-1', $normalizedName, $uploadedFile);
        $this->injector->alias(BristolStairImageStorageInfoRepo::class, FakeBristolStairImageStorageInfoRepo::class);
        $this->injector->share($repo);

        $this->injector->defineParam('stored_stair_image_file_id', $fileId);

        $result = $this->injector->execute([BristolStairs::class, 'getImage']);

        $this->assertInstanceOf(StoredFileErrorResponse::class, $result);
        $this->assertStringContainsString($normalizedName, $result->getBody());
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::handleFileUpload
     */
    public function test_handleFileUpload_returns_stub_response_when_no_file(): void
    {
        $this->setupFakeUserSession();
        $uploadedFiles = new FakeUploadedFiles([]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $varMap = new ArrayVarMap([]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([BristolStairs::class, 'handleFileUpload']);

        $this->assertInstanceOf(StubResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::handleFileUpload
     */
    public function test_handleFileUpload_returns_json_error_when_storage_returns_UploadError(): void
    {
        $this->setupFakeUserSession();
        $storage = new BristolStairImageStorageReturningUploadError();
        $this->injector->alias(BristolStairImageStorage::class, BristolStairImageStorageReturningUploadError::class);
        $this->injector->share($storage);

        $tmpFile = tmpfile();
        $this->assertNotFalse($tmpFile);
        $meta = stream_get_meta_data($tmpFile);
        $uploadedFile = new UploadedFile($meta['uri'], 10, 'stair.jpg', 0);
        $uploadedFiles = new FakeUploadedFiles([BristolStairs::BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $varMap = new ArrayVarMap([]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([BristolStairs::class, 'handleFileUpload']);

        $this->assertInstanceOf(JsonNoCacheResponse::class, $result);
        $this->assertStringContainsString('error', $result->getBody());
        fclose($tmpFile);
    }

    /**
     * @covers \Bristolian\AppController\BristolStairs::handleFileUpload
     */
    public function test_handleFileUpload_returns_UploadBristolStairsImageResponse_on_success(): void
    {
        $this->setupFakeUserSession();
        $tmpFile = tmpfile();
        $this->assertNotFalse($tmpFile);
        $meta = stream_get_meta_data($tmpFile);
        $uploadedFile = new UploadedFile($meta['uri'], 10, 'stair.jpg', 0);
        $uploadedFiles = new FakeUploadedFiles([BristolStairs::BRISTOL_STAIRS_FILE_UPLOAD_FORM_NAME => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $varMap = new ArrayVarMap([]);
        $this->injector->alias(VarMap::class, ArrayVarMap::class);
        $this->injector->share($varMap);

        $result = $this->injector->execute([BristolStairs::class, 'handleFileUpload']);

        $this->assertInstanceOf(UploadBristolStairsImageResponse::class, $result);
        fclose($tmpFile);
    }
}
