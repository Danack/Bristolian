<?php

namespace BristolianTest\Model\Types;

use BristolianTest\BaseTestCase;
//use BristolStairImageFile;


use Bristolian\Model\Generated\BristolStairInfo;

/**
 * @coversNothing
 */
class BristolStairImageFileTest extends BaseTestCase
{
    /**
     * @covers \BristolStairImageFile
     */
    public function testConstruct()
    {
        $id = 'stair-image-123';
        $userId = 'user-456';
        $normalizedName = 'stair_image.jpg';
        $originalFilename = 'Stair Image.jpg';
        $size = 123456;
        $state = 'active';
        $createdAt = new \DateTimeImmutable();

        $imageFile = new BristolStairImageFile(
            $id,
            $userId,
            $normalizedName,
            $originalFilename,
            $size,
            $state,
            $createdAt
        );

        $this->assertSame($id, $imageFile->id);
        $this->assertSame($userId, $imageFile->user_id);
        $this->assertSame($normalizedName, $imageFile->normalized_name);
        $this->assertSame($originalFilename, $imageFile->original_filename);
        $this->assertSame($size, $imageFile->size);
        $this->assertSame($state, $imageFile->state);
        $this->assertSame($createdAt, $imageFile->created_at);
    }

    /**
     * @covers \BristolStairImageFile
     */
    public function testToArray()
    {
        $imageFile = new BristolStairImageFile(
            'id-123',
            'user-456',
            'normalized.jpg',
            'Original.jpg',
            100,
            'active',
            new \DateTimeImmutable()
        );

        $array = $imageFile->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('normalized_name', $array);
    }
}

