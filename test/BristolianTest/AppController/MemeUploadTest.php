<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Bristolian\AppController\MemeUpload;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\MemeUploadSuccessResponse;
use Bristolian\Service\ObjectStore\FakeMemeObjectStore;
use Bristolian\Service\ObjectStore\MemeObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\StubResponse;

/**
 * @coversNothing
 */
class MemeUploadTest extends BaseTestCase
{
    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
        $memeObjectStore = new FakeMemeObjectStore();
        $this->injector->alias(MemeObjectStore::class, FakeMemeObjectStore::class);
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
        $uploadedFiles = new class implements UploadedFiles {
            public function get(string $form_name): UploadedFile|null
            {
                return null;
            }
        };
        $this->injector->alias(UploadedFiles::class, get_class($uploadedFiles));
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
        $uploadedFiles = new class($uploadedFile) implements UploadedFiles {
            public function __construct(private UploadedFile $file)
            {
            }
            public function get(string $form_name): UploadedFile
            {
                return $this->file;
            }
        };
        $this->injector->alias(UploadedFiles::class, get_class($uploadedFiles));
        $this->injector->share($uploadedFiles);

        $result = $this->injector->execute([MemeUpload::class, 'handleMemeUpload']);

        $this->assertInstanceOf(MemeUploadSuccessResponse::class, $result);
        fclose($tmpFile);
    }
}
