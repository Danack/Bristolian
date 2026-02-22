<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Filesystem\AvatarImageFilesystem;
use Bristolian\Service\ObjectStore\StandardAvatarImageObjectStore;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * @coversNothing
 */
class StandardAvatarImageObjectStoreTest extends BaseTestCase
{
    private ?string $testDir = null;

    public function tearDown(): void
    {
        if ($this->testDir !== null && is_dir($this->testDir)) {
            $file = $this->testDir . '/avatar.png';
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($this->testDir);
        }
        parent::tearDown();
    }

    /**
     * @covers \Bristolian\Service\ObjectStore\StandardAvatarImageObjectStore::__construct
     * @covers \Bristolian\Service\ObjectStore\StandardAvatarImageObjectStore::upload
     */
    public function test_upload_writes_file_via_avatar_filesystem(): void
    {
        $this->testDir = __DIR__ . '/StandardAvatarImageObjectStoreTest_fs_' . uniqid();
        mkdir($this->testDir);

        $adapter = new LocalFilesystemAdapter($this->testDir);
        $filesystem = new AvatarImageFilesystem($adapter);
        $store = new StandardAvatarImageObjectStore($filesystem);

        $store->upload('avatar.png', 'image data');

        $path = $this->testDir . '/avatar.png';
        $this->assertFileExists($path);
        $this->assertSame('image data', file_get_contents($path));
    }
}
