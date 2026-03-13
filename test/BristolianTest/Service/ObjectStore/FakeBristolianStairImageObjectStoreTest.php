<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeBristolianStairImageObjectStoreTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::upload
     * @covers \Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::hasFile
     * @covers \Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::getFileContents
     */
    public function test_upload_stores_content_and_hasFile_getFileContents_work(): void
    {
        $store = new FakeBristolianStairImageObjectStore();
        $filename = 'stair-' . create_test_uniqid() . '.jpg';
        $contents = 'stair image data';

        $store->upload($filename, $contents);

        $this->assertTrue($store->hasFile($filename));
        $this->assertSame($contents, $store->getFileContents($filename));
    }

    /**
     * @covers \Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::hasFile
     */
    public function test_hasFile_returns_false_for_missing_file(): void
    {
        $store = new FakeBristolianStairImageObjectStore();
        $this->assertFalse($store->hasFile('nonexistent.jpg'));
    }
}
