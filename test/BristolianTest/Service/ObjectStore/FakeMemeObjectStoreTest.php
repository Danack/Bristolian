<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Service\ObjectStore\FakeMemeObjectStore;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\ObjectStore\FakeMemeObjectStore::class, '__construct')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeMemeObjectStore::class, 'getFileContents')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeMemeObjectStore::class, 'getStoredFiles')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeMemeObjectStore::class, 'hasFile')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeMemeObjectStore::class, 'upload')]

class FakeMemeObjectStoreTest extends BaseTestCase
{
    public function test_upload_stores_content_and_hasFile_getFileContents_getStoredFiles_work(): void
    {
        $store = new FakeMemeObjectStore();
        $filename = 'test-' . create_test_uniqid() . '.jpg';
        $contents = 'binary image data';

        $store->upload($filename, $contents);

        $this->assertTrue($store->hasFile($filename));
        $this->assertSame($contents, $store->getFileContents($filename));
        $stored = $store->getStoredFiles();
        $this->assertCount(1, $stored);
        $this->assertArrayHasKey($filename, $stored);
        $this->assertSame($contents, $stored[$filename]);
    }

    public function test_hasFile_returns_false_for_missing_file(): void
    {
        $store = new FakeMemeObjectStore();
        $this->assertFalse($store->hasFile('nonexistent.jpg'));
    }
}
