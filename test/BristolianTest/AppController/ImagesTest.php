<?php

declare(strict_types=1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Images;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;
use SlimDispatcher\Response\ImageResponse;

/**
 * @coversNothing
 */
class ImagesTest extends BaseTestCase
{
    private string $tempRoot;

    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
        $this->tempRoot = sys_get_temp_dir() . '/bristolian_images_test_' . uniqid();
        mkdir($this->tempRoot, 0755, true);
        $adapter = new LocalFilesystemAdapter($this->tempRoot);
        $this->injector->share(new MemeFilesystem($adapter, []));
        $this->injector->share(new LocalCacheFilesystem($adapter, $this->tempRoot));
    }

    /**
     * @covers \Bristolian\AppController\Images::show_meme
     */
    public function test_show_meme_throws_when_meme_not_found(): void
    {
        $this->injector->defineParam('id', 'nonexistent-meme-id');

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('meme with id (nonexistent-meme-id) not found');

        $this->injector->execute([Images::class, 'show_meme']);
    }

    /**
     * @covers \Bristolian\AppController\Images::show_meme
     */
    public function test_show_meme_returns_image_response_when_meme_exists_and_file_cached(): void
    {
        $normalizedName = 'meme-' . uniqid() . '.jpg';
        $tempFilePath = $this->tempRoot . '/' . $normalizedName;
        $imageContent = 'fake image bytes';
        file_put_contents($tempFilePath, $imageContent);

        $memeFilesystem = $this->injector->make(MemeFilesystem::class);
        $memeFilesystem->write($normalizedName, $imageContent);

        $uploadedFile = new UploadedFile($tempFilePath, strlen($imageContent), 'meme.jpg', 0);
        $memeStorageRepo = $this->injector->make(FakeMemeStorageRepo::class);
        $memeId = $memeStorageRepo->storeMeme('test-user-id', $normalizedName, $uploadedFile);

        $this->injector->defineParam('id', $memeId);

        $result = $this->injector->execute([Images::class, 'show_meme']);

        $this->assertInstanceOf(ImageResponse::class, $result);
    }
}
