<?php

namespace BristolianTest\Service\ObjectStore;

use BristolianTest\BaseTestCase;
use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use BristolianTest\Repo\TestPlaceholders;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Service\ObjectStore\FakeRoomFileObjectStore::class)]

class FakeRoomFileObjectStoreTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $objectStore = new FakeRoomFileObjectStore();

        $name = $this->getTestObjectName();

        $message = "beep-boop I am a test file";

        $objectStore->upload($name, $message);
        $this->assertTrue($objectStore->hasFile($name));
        $this->assertSame($message, $objectStore->getFileContents($name));

        $this->assertCount(1, $objectStore->getStoredFiles());
    }
}
