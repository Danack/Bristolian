<?php

declare(strict_types=1);

namespace BristolianTest\Service\ObjectStore;

use Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore::class, 'getFileContents')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore::class, 'getStoredFiles')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore::class, 'hasFile')]
#[CoversMethod(\Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore::class, 'upload')]

class FakeAvatarImageObjectStoreTest extends BaseTestCase
{
    public function test_upload_stores_content_and_hasFile_getFileContents_getStoredFiles_work(): void
    {
        $store = new FakeAvatarImageObjectStore();
        $filename = 'avatar-' . create_test_uniqid() . '.jpg';
        $contents = 'image data';

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
        $store = new FakeAvatarImageObjectStore();
        $this->assertFalse($store->hasFile('nonexistent.jpg'));
    }
}
