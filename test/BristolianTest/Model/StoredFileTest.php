<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use StoredFile;

/**
 * @coversNothing
 */
class StoredFileTest extends BaseTestCase
{
    /**
     * @covers \StoredFile
     */
    public function testConstruct()
    {
        $id = 'file-123';
        $normalizedName = 'file_name.txt';
        $originalFilename = 'File Name.txt';
        $state = 'active';
        $size = 1024;
        $userId = 'user-456';
        $createdAt = new \DateTimeImmutable();

        $storedFile = new StoredFile(
            $id,
            $normalizedName,
            $originalFilename,
            $state,
            $size,
            $userId,
            $createdAt
        );

        $this->assertSame($id, $storedFile->id);
        $this->assertSame($normalizedName, $storedFile->normalized_name);
        $this->assertSame($originalFilename, $storedFile->original_filename);
        $this->assertSame($state, $storedFile->state);
        $this->assertSame($size, $storedFile->size);
        $this->assertSame($userId, $storedFile->user_id);
        $this->assertSame($createdAt, $storedFile->created_at);
    }

    /**
     * @covers \StoredFile
     */
    public function testToArray()
    {
        $storedFile = new StoredFile(
            'id',
            'normalized.txt',
            'Original.txt',
            'active',
            100,
            'user-id',
            new \DateTimeImmutable()
        );

        $array = $storedFile->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('normalized_name', $array);
    }
}

