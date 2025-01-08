<?php

namespace BristolianTest\Service\MemeStorageProcessor;

use BristolianTest\BaseTestCase;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;

/**
 * @covers \Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme
 */
class ObjectStoredMemeTest extends BaseTestCase
{
    public function testWorks()
    {
        $normalized_filename = "foo";
        $fileStorageId = '12345';

        $object = new ObjectStoredMeme($normalized_filename, $fileStorageId);
        $this->assertSame($normalized_filename, $object->normalized_filename);
        $this->assertSame($fileStorageId, $object->meme_id);
    }
}
