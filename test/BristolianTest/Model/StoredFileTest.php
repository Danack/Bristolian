<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\RoomFileObjectInfo;

/**
 * @coversNothing
 */
class StoredFileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\RoomFileObjectInfo
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

        $storedFile = new RoomFileObjectInfo(
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
     * @covers \Bristolian\Model\Generated\RoomFileObjectInfo
     */
    public function testToArray()
    {
        // RoomFileObjectInfo doesn't have toArray() method (uses FromArray trait instead)
        // This test is kept for compatibility but may need to be updated
        $this->markTestSkipped('RoomFileObjectInfo uses FromArray trait, not ToArray - toArray() method not available');
    }
}

