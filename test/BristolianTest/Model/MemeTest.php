<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Meme;

/**
 * @coversNothing
 */
class MemeTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Meme
     */
    public function testConstruct()
    {
        $id = 'meme-123';
        $normalizedName = 'meme_image.jpg';
        $originalFilename = 'Meme Image.jpg';
        $state = 'active';
        $size = 654321;
        $userId = 'user-789';

//        $meme = new Meme(
//            $id,
//            $normalizedName,
//            $originalFilename,
//            $state,
//            $size,
//            $userId
//        );
//
//        $this->assertSame($id, $meme->id);
//        $this->assertSame($normalizedName, $meme->normalized_name);
//        $this->assertSame($originalFilename, $meme->original_filename);
//        $this->assertSame($state, $meme->state);
//        $this->assertSame($size, $meme->size);
//        $this->assertSame($userId, $meme->user_id);
    }

    /**
     * @covers \Bristolian\Model\Meme
     */
    public function testToArray()
    {
//        $meme = new Meme(
//            'id-123',
//            'normalized.jpg',
//            'Original.jpg',
//            'active',
//            100,
//            'user-id'
//        );
//
//        $array = $meme->toArray();
//        $this->assertArrayHasKey('id', $array);
//        $this->assertArrayHasKey('normalized_name', $array);
    }
}

