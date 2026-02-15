<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\Meme;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class MemeTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\Meme
     */
    public function testConstruct(): void
    {
        $id = 'meme-123';
        $userId = 'user-789';
        $normalizedName = 'meme_image.jpg';
        $originalFilename = 'Meme Image.jpg';
        $state = 'active';
        $size = 654321;
        $createdAt = new \DateTimeImmutable();

        $meme = new Meme($id, $userId, $normalizedName, $originalFilename, $state, $size, $createdAt);

        $this->assertSame($id, $meme->id);
        $this->assertSame($userId, $meme->user_id);
        $this->assertSame($normalizedName, $meme->normalized_name);
        $this->assertSame($originalFilename, $meme->original_filename);
        $this->assertSame($state, $meme->state);
        $this->assertSame($size, $meme->size);
        $this->assertSame($createdAt, $meme->created_at);
        $this->assertFalse($meme->deleted);
    }

    /**
     * @covers \Bristolian\Model\Types\Meme
     */
    public function testToArray(): void
    {
        $meme = new Meme(
            'id-123',
            'user-id',
            'normalized.jpg',
            'Original.jpg',
            'active',
            100,
            new \DateTimeImmutable()
        );

        $array = $meme->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('normalized_name', $array);
    }
}
