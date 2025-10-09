<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\BristolStairInfo;

/**
 * @coversNothing
 */
class BristolStairInfoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\BristolStairInfo
     */
    public function testConstruct()
    {
        $id = 'stair-info-123';
        $latitude = '51.454513';
        $longitude = '-2.587910';
        $description = 'A nice set of stairs';
        $storedStairImageFileId = 'image-456';
        $steps = 42;
        $isDeleted = 0;
        $createdAt = new \DateTimeImmutable();
        $updatedAt = new \DateTimeImmutable();

        $stairInfo = new BristolStairInfo(
            $id,
            $latitude,
            $longitude,
            $description,
            $storedStairImageFileId,
            $steps,
            $isDeleted,
            $createdAt,
            $updatedAt
        );

        $this->assertSame($id, $stairInfo->id);
        $this->assertSame($latitude, $stairInfo->latitude);
        $this->assertSame($longitude, $stairInfo->longitude);
        $this->assertSame($description, $stairInfo->description);
        $this->assertSame($storedStairImageFileId, $stairInfo->stored_stair_image_file_id);
        $this->assertSame($steps, $stairInfo->steps);
        $this->assertSame($isDeleted, $stairInfo->is_deleted);
        $this->assertSame($createdAt, $stairInfo->created_at);
        $this->assertSame($updatedAt, $stairInfo->updated_at);
    }

    /**
     * @covers \Bristolian\Model\BristolStairInfo
     */
    public function testToArray()
    {
        $stairInfo = new BristolStairInfo(
            'id-123',
            '51.454513',
            '-2.587910',
            'Description',
            'image-id',
            10,
            0,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $array = $stairInfo->toArray();
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('latitude', $array);
        $this->assertArrayHasKey('steps', $array);
    }
}

