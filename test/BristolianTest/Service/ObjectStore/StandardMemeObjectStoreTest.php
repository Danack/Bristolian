<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Service\ObjectStore\StandardMemeObjectStore;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * @coversNothing
 */
class StandardMemeObjectStoreTest extends BaseTestCase
{
    private ?string $testDir = null;

    public function tearDown(): void
    {
        if ($this->testDir !== null && is_dir($this->testDir)) {
            $file = $this->testDir . '/test-meme.jpg';
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($this->testDir);
        }
        parent::tearDown();
    }

    /**
     * @covers \Bristolian\Service\ObjectStore\StandardMemeObjectStore::__construct
     * @covers \Bristolian\Service\ObjectStore\StandardMemeObjectStore::upload
     */
    public function test_upload_writes_file_via_meme_filesystem(): void
    {
        $this->testDir = __DIR__ . '/StandardMemeObjectStoreTest_fs_' . uniqid();
        mkdir($this->testDir);

        $adapter = new LocalFilesystemAdapter($this->testDir);
        $memeFilesystem = new MemeFilesystem($adapter, []);
        $store = new StandardMemeObjectStore($memeFilesystem);

        $store->upload('test-meme.jpg', 'binary image contents');

        $path = $this->testDir . '/test-meme.jpg';
        $this->assertFileExists($path);
        $this->assertSame('binary image contents', file_get_contents($path));
    }
}
