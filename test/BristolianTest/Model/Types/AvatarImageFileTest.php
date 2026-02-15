<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\AvatarImageFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class AvatarImageFileTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\AvatarImageFile
     */
    public function test_construct(): void
    {
        $id = 'avatar-id-123';
        $userId = 'user-456';
        $normalizedName = 'avatar_normalized.jpg';
        $originalFilename = 'Avatar Image.jpg';
        $size = 1024;
        $state = 'active';
        $createdAt = new \DateTimeImmutable();

        $file = new AvatarImageFile($id, $userId, $normalizedName, $originalFilename, $size, $state, $createdAt);

        $this->assertSame($id, $file->id);
        $this->assertSame($userId, $file->user_id);
        $this->assertSame($normalizedName, $file->normalized_name);
        $this->assertSame($originalFilename, $file->original_filename);
        $this->assertSame($size, $file->size);
        $this->assertSame($state, $file->state);
        $this->assertSame($createdAt, $file->created_at);
    }
}
