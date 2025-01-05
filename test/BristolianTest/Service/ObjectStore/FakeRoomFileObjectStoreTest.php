<?php

namespace BristolianTest\Service\ObjectStore;

use BristolianTest\BaseTestCase;
use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @coversNothing
 */
class FakeRoomFileObjectStoreTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Service\ObjectStore\FakeRoomFileObjectStore
     */
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
