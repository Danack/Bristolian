<?php

namespace BristolianTest\Exception;

use Bristolian\Exception\ContentNotFoundException;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Exception\ContentNotFoundException::class)]

class ContentNotFoundExceptionTest extends BaseTestCase
{
    public function testWorks_stairs_id_not_found()
    {
        $stairs_id = "stairs_123";

        $exception = ContentNotFoundException::stairs_id_not_found($stairs_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($stairs_id, $exception->getMessage());
        $this->assertStringContainsString("stairs with id", $exception->getMessage());
    }

    public function testWorks_meme_id_not_found()
    {
        $meme_id = "meme_456";

        $exception = ContentNotFoundException::meme_id_not_found($meme_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($meme_id, $exception->getMessage());
        $this->assertStringContainsString("meme with id", $exception->getMessage());
    }

    public function testWorks_file_not_found()
    {
        $room_id = "room_abc";
        $file_id = "file_789";

        $exception = ContentNotFoundException::file_not_found($room_id, $file_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($room_id, $exception->getMessage());
        $this->assertStringContainsString($file_id, $exception->getMessage());
        $this->assertStringContainsString("file with id", $exception->getMessage());
    }

    public function testWorks_room_video_not_found()
    {
        $room_id = "room_xyz";
        $room_video_id = "rv_123";

        $exception = ContentNotFoundException::room_video_not_found($room_id, $room_video_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($room_id, $exception->getMessage());
        $this->assertStringContainsString($room_video_id, $exception->getMessage());
        $this->assertStringContainsString("room video with id", $exception->getMessage());
    }

    public function testWorks_video_not_found()
    {
        $video_id = "video_999";

        $exception = ContentNotFoundException::video_not_found($video_id);

        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($video_id, $exception->getMessage());
        $this->assertStringContainsString("video with id", $exception->getMessage());
    }

    public function test_room_video_not_found_by_id(): void
    {
        $roomVideoId = "rv_456";
        $exception = ContentNotFoundException::room_video_not_found_by_id($roomVideoId);
        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertStringContainsString($roomVideoId, $exception->getMessage());
        $this->assertStringContainsString("room video with id", $exception->getMessage());
    }

    public function test_transcript_not_found(): void
    {
        $exception = ContentNotFoundException::transcript_not_found("trans_123");
        $this->assertInstanceOf(ContentNotFoundException::class, $exception);
        $this->assertSame("Transcript not found", $exception->getMessage());
    }

    public function test_room_note_not_found(): void
    {
        $exception = ContentNotFoundException::room_note_not_found('room_1', 'note_1');
        $this->assertStringContainsString('note_1', $exception->getMessage());
        $this->assertStringContainsString('room_1', $exception->getMessage());
    }
}
