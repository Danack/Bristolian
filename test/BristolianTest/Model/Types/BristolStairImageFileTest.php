<?php

namespace BristolianTest\Model\Types;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\StairImageObjectInfo;

/**
 * @coversNothing
 */
class BristolStairImageFileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\StairImageObjectInfo
     */
    public function testConstruct()
    {
        $id = 'stair-image-123';
        $normalizedName = 'stair_image.jpg';
        $originalFilename = 'Stair Image.jpg';
        $state = 'active';
        $size = 123456;
        $userId = 'user-456';
        $createdAt = new \DateTimeImmutable();

        $imageFile = new StairImageObjectInfo(
            $id,
            $normalizedName,
            $originalFilename,
            $state,
            $size,
            $userId,
            $createdAt
        );

        $this->assertSame($id, $imageFile->id);
        $this->assertSame($normalizedName, $imageFile->normalized_name);
        $this->assertSame($originalFilename, $imageFile->original_filename);
        $this->assertSame($state, $imageFile->state);
        $this->assertSame($size, $imageFile->size);
        $this->assertSame($userId, $imageFile->user_id);
        $this->assertSame($createdAt, $imageFile->created_at);
    }

    /**
     * @covers \Bristolian\Model\Generated\StairImageObjectInfo
     */
    public function testToArray()
    {
        // StairImageObjectInfo doesn't have toArray() method (uses FromArray trait instead)
        // This test is kept for compatibility but may need to be updated
        $this->markTestSkipped('StairImageObjectInfo uses FromArray trait, not ToArray - toArray() method not available');
    }
}

