<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use PHPUnit\Framework\Attributes\CoversMethod;
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
use BristolianGenerated\Response\GetBristolStairsResponse;
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
use function Safe\fclose;
use function Safe\file_put_contents;
use function Safe\json_decode;
use function Safe\mkdir;
use function Safe\tmpfile;

/**
 * BristolStairImageStorage that always returns UploadError for testing error path.
 *
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

#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'getData')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'getImage')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'getOpenmapNearby')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'handleFileUpload')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'render_stairs_page')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'stairs_page')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'stairs_page_stair_selected')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'update_stairs_info')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'update_stairs_info_get')]
#[CoversMethod(\Bristolian\AppController\BristolStairs::class, 'update_stairs_position')]

class BristolStairsTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
    }

    public function test_update_stairs_info_get(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'update_stairs_info_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    public function test_stairs_page(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'stairs_page']);
        $this->assertIsString($result);
        $this->assertStringContainsString('A map of Bristol Stairs', $result);
        $this->assertStringContainsString('bristol_stairs_map', $result);
        $this->assertStringContainsString('flights of stairs', $result);
        $this->assertStringContainsString('steps', $result);
    }

    public function test_stairs_page_stair_selected(): void
    {
        $this->injector->defineParam('stair_id', 1);
        $result = $this->injector->execute([BristolStairs::class, 'stairs_page_stair_selected']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Steep stairs near Park Street', $result);
        $this->assertStringContainsString('flights of stairs', $result);
        $this->assertStringContainsString('steps', $result);
    }

    public function test_getData(): void
    {
        $result = $this->injector->execute([BristolStairs::class, 'getData']);
        $this->assertInstanceOf(GetBristolStairsResponse::class, $result);
    }

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
