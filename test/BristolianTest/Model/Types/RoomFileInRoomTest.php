<?php

declare(strict_types=1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\RoomFileInRoom;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomFileInRoomTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\RoomFileInRoom::__construct
     */
    public function test_constructor_sets_all_properties(): void
    {
        $id = 'file-id-123';
        $normalizedName = 'normalized.pdf';
        $originalFilename = 'Document.pdf';
        $state = 'uploaded';
        $size = 2048;
        $userId = 'user-456';
        $createdAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $documentTimestamp = new \DateTimeImmutable('2024-01-15 11:30:00');

        $roomFile = new RoomFileInRoom(
            $id,
            $normalizedName,
            $originalFilename,
            $state,
            $size,
            $userId,
            $createdAt,
            $documentTimestamp,
            'List description',
            'A longer note.'
        );

        $this->assertSame($id, $roomFile->id);
        $this->assertSame($normalizedName, $roomFile->normalized_name);
        $this->assertSame($originalFilename, $roomFile->original_filename);
        $this->assertSame($state, $roomFile->state);
        $this->assertSame($size, $roomFile->size);
        $this->assertSame($userId, $roomFile->user_id);
        $this->assertSame($createdAt, $roomFile->created_at);
        $this->assertSame($documentTimestamp, $roomFile->document_timestamp);
        $this->assertSame('List description', $roomFile->description);
        $this->assertSame('A longer note.', $roomFile->note);
    }

    /**
     * @covers \Bristolian\Model\Types\RoomFileInRoom::__construct
     */
    public function test_constructor_accepts_null_document_timestamp(): void
    {
        $roomFile = new RoomFileInRoom(
            'file-id',
            'normalized.pdf',
            'original.pdf',
            'uploaded',
            1024,
            'user-1',
            new \DateTimeImmutable(),
            null,
            null,
            null
        );

        $this->assertNull($roomFile->document_timestamp);
        $this->assertNull($roomFile->description);
        $this->assertNull($roomFile->note);
    }
}
