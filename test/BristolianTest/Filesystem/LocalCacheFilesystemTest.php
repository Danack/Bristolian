<?php

namespace BristolianTest\Filesystem;

use Bristolian\Filesystem\LocalCacheFilesystem;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Filesystem\LocalCacheFilesystem
 */
class LocalCacheFilesystemTest extends BaseTestCase
{
    public function testWorks()
    {
        $rootLocation = __DIR__ . "/../../temp/";
        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($rootLocation);
        $filesystem = new \Bristolian\Filesystem\LocalCacheFilesystem($adapter, $rootLocation);
        $this->assertSame($rootLocation, $filesystem->getFullPath());
    }
}
