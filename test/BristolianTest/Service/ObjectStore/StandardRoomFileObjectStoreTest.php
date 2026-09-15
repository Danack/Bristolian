<?php

namespace BristolianTest\Service\ObjectStore;

use BristolianTest\BaseTestCase;
use Bristolian\Service\ObjectStore\StandardRoomFileObjectStore;
use BristolianTest\Repo\TestPlaceholders;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @group scaleway
 * @group external
 */
#[CoversNothing]
class StandardRoomFileObjectStoreTest extends BaseTestCase
{
    use TestPlaceholders;

    public function testWorks()
    {
        $objectStore = $this->injector->make(StandardRoomFileObjectStore::class);

        $name = $this->getTestObjectName();

        $objectStore->upload($name, "beep-boop I am a test file");
    }
}
