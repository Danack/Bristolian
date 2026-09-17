<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::class, 'getFileContents')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::class, 'hasFile')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeBristolianStairImageObjectStore::class, 'upload')]

class FakeBristolianStairImageObjectStoreTest extends BaseTestCase
{
    public function test_upload_stores_content_and_hasFile_getFileContents_work(): void
    {
        $store = new FakeBristolianStairImageObjectStore();
        $filename = 'stair-' . create_test_uniqid() . '.jpg';
        $contents = 'stair image data';

        $store->upload($filename, $contents);

        $this->assertTrue($store->hasFile($filename));
        $this->assertSame($contents, $store->getFileContents($filename));
    }

    public function test_hasFile_returns_false_for_missing_file(): void
    {
        $store = new FakeBristolianStairImageObjectStore();
        $this->assertFalse($store->hasFile('nonexistent.jpg'));
    }
}
