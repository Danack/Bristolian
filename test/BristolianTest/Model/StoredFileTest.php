<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use BristolianGenerated\Model\RoomFileObjectInfo;
use Safe\DateTimeImmutable;

/**
 * @coversNothing
 */
class StoredFileTest extends BaseTestCase
{
    /**
     * @covers \BristolianGenerated\Model\RoomFileObjectInfo
     */
    public function testConstruct()
    {
        $id = 'file-123';
        $normalizedName = 'file_name.txt';
        $originalFilename = 'File Name.txt';
        $state = 'active';
        $size = 1024;
        $userId = 'user-456';
        $createdAt = new DateTimeImmutable();

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
}
