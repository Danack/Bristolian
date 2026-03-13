<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Service\ObjectStore\StandardRoomFileObjectStore;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Unit test for StandardRoomFileObjectStore using a local filesystem (no external storage).
 *
 * @coversNothing
 */
class StandardRoomFileObjectStoreLocalTest extends BaseTestCase
{
    private ?string $testDir = null;

    public function tearDown(): void
    {
        if ($this->testDir !== null && is_dir($this->testDir)) {
            $file = $this->testDir . '/roomfile.txt';
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($this->testDir);
        }
        parent::tearDown();
    }

    /**
     * @covers \Bristolian\Service\ObjectStore\StandardRoomFileObjectStore::__construct
     * @covers \Bristolian\Service\ObjectStore\StandardRoomFileObjectStore::upload
     */
    public function test_upload_writes_file_via_room_file_filesystem(): void
    {
        $this->testDir = sys_get_temp_dir() . '/room_file_store_' . uniqid();
        mkdir($this->testDir);

        $adapter = new LocalFilesystemAdapter($this->testDir);
        $filesystem = new RoomFileFilesystem($adapter);
        $store = new StandardRoomFileObjectStore($filesystem);

        $store->upload('roomfile.txt', 'room file contents');

        $path = $this->testDir . '/roomfile.txt';
        $this->assertFileExists($path);
        $this->assertSame('room file contents', file_get_contents($path));
    }
}
