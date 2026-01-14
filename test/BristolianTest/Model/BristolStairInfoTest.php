<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\BristolStairInfo;

/**
 * @coversNothing
 */
class BristolStairInfoTest extends BaseTestCase
{
    /**
     * @covers Bristolian\Model\Generated\BristolStairInfo
     */
    public function testConstruct()
    {
        $id = 123;
        $description = 'A nice set of stairs';
        $latitude = 51.454513;
        $longitude = -2.587910;
        $storedStairImageFileId = 'image-456';
        $steps = 42;
        $isDeleted = 0;
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable();

        $stairInfo = new BristolStairInfo(
            $id,
            $description,
            $latitude,
            $longitude,
            $storedStairImageFileId,
            $steps,
            $isDeleted,
            $createdAt,
            $updatedAt
        );

        $this->assertSame($id, $stairInfo->id);
        $this->assertSame($description, $stairInfo->description);
        $this->assertSame($latitude, $stairInfo->latitude);
        $this->assertSame($longitude, $stairInfo->longitude);
        $this->assertSame($storedStairImageFileId, $stairInfo->stored_stair_image_file_id);
        $this->assertSame($steps, $stairInfo->steps);
        $this->assertSame($isDeleted, $stairInfo->is_deleted);
        $this->assertSame($createdAt, $stairInfo->created_at);
        $this->assertSame($updatedAt, $stairInfo->updated_at);
    }

    /**
     * @covers \Bristolian\Model\Generated\BristolStairInfo
     */
    public function testToArray()
    {
        // BristolStairInfo doesn't have toArray() method (uses FromArray trait instead)
        // This test is kept for compatibility but may need to be updated
        $this->markTestSkipped('BristolStairInfo uses FromArray trait, not ToArray - toArray() method not available');
    }
}

