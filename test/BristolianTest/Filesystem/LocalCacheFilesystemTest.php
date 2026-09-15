<?php

namespace BristolianTest\Filesystem;

use Bristolian\Filesystem\LocalCacheFilesystem;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Filesystem\LocalCacheFilesystem::class)]
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
