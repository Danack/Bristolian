<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Bristolian\AppController\MemeUpload;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\MemeUploadErrorResponse;
use Bristolian\Response\MemeUploadSuccessResponse;
use Bristolian\Service\MemeStorageProcessor\MemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\StubResponse;

/**
 * MemeStorageProcessor that always returns UploadError for coverage of error path.
 *
 * @coversNothing
 */
final class MemeStorageProcessorReturningUploadError implements MemeStorageProcessor
{
    public function storeMemeForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        MemeObjectStore $fileObjectStore
    ): UploadError {
        return UploadError::unsupportedFileType();
    }
}

/**
 * @coversNothing
 */
class MemeUploadTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
        $memeObjectStore = new \Bristolian\Service\ObjectStore\FakeMemeObjectStore();
        $this->injector->alias(MemeObjectStore::class, \Bristolian\Service\ObjectStore\FakeMemeObjectStore::class);
        $this->injector->share($memeObjectStore);
    }

    /**
     * @covers \Bristolian\AppController\MemeUpload::handleMemeUpload_get
     */
    public function test_handleMemeUpload_get(): void
    {
        $result = $this->injector->execute([MemeUpload::class, 'handleMemeUpload_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\MemeUpload::handleMemeUpload
     */
    public function test_handleMemeUpload_returns_error_when_no_file_uploaded(): void
    {
        $this->setupFakeUserSession();
        $uploadedFiles = new FakeUploadedFiles([]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);

        $result = $this->injector->execute([MemeUpload::class, 'handleMemeUpload']);

        $this->assertInstanceOf(StubResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\MemeUpload::handleMemeUpload
     */
    public function test_handleMemeUpload_returns_success_when_file_uploaded(): void
    {
        $this->setupFakeUserSession();
        $tmpFile = tmpfile();
        $this->assertNotFalse($tmpFile);
        $meta = stream_get_meta_data($tmpFile);
        $tmpPath = $meta['uri'];
        $uploadedFile = new UploadedFile($tmpPath, 10, 'test.pdf', 0);
        $uploadedFiles = new FakeUploadedFiles([MemeUpload::MEME_FILE_UPLOAD_FORM_NAME => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);

        $result = $this->injector->execute([MemeUpload::class, 'handleMemeUpload']);

        $this->assertInstanceOf(MemeUploadSuccessResponse::class, $result);
        fclose($tmpFile);
    }

    /**
     * @covers \Bristolian\AppController\MemeUpload::handleMemeUpload
     */
    public function test_handleMemeUpload_returns_MemeUploadErrorResponse_when_storage_returns_error(): void
    {
        $this->setupFakeUserSession();
        $storage = new MemeStorageProcessorReturningUploadError();
        $this->injector->alias(MemeStorageProcessor::class, MemeStorageProcessorReturningUploadError::class);
        $this->injector->share($storage);

        $tmpFile = tmpfile();
        $this->assertNotFalse($tmpFile);
        $meta = stream_get_meta_data($tmpFile);
        $tmpPath = $meta['uri'];
        $uploadedFile = new UploadedFile($tmpPath, 10, 'test.pdf', 0);
        $uploadedFiles = new FakeUploadedFiles([MemeUpload::MEME_FILE_UPLOAD_FORM_NAME => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);

        $result = $this->injector->execute([MemeUpload::class, 'handleMemeUpload']);

        $this->assertInstanceOf(MemeUploadErrorResponse::class, $result);
        fclose($tmpFile);
    }
}
