<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Filesystem\BristolStairsFilesystem;
use Bristolian\Service\ObjectStore\StandardBristolianStairImageObjectStore;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Unit test for StandardBristolianStairImageObjectStore using a local filesystem (no external storage).
 *
 * @coversNothing
 */
class StandardBristolianStairImageObjectStoreTest extends BaseTestCase
{
    private ?string $testDir = null;

    public function tearDown(): void
    {
        if ($this->testDir !== null && is_dir($this->testDir)) {
            $file = $this->testDir . '/stair.jpg';
            if (is_file($file)) {
                unlink($file);
            }
            rmdir($this->testDir);
        }
        parent::tearDown();
    }

    /**
     * @covers \Bristolian\Service\ObjectStore\StandardBristolianStairImageObjectStore::__construct
     * @covers \Bristolian\Service\ObjectStore\StandardBristolianStairImageObjectStore::upload
     */
    public function test_upload_writes_file_via_bristol_stairs_filesystem(): void
    {
        $this->testDir = sys_get_temp_dir() . '/bristol_stair_image_store_' . uniqid();
        mkdir($this->testDir);

        $adapter = new LocalFilesystemAdapter($this->testDir);
        $filesystem = new BristolStairsFilesystem($adapter);
        $store = new StandardBristolianStairImageObjectStore($filesystem);

        $store->upload('stair.jpg', 'stair image contents');

        $path = $this->testDir . '/stair.jpg';
        $this->assertFileExists($path);
        $this->assertSame('stair image contents', file_get_contents($path));
    }
}
