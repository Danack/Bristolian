<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomVideoTranscriptRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\Model\Types\RoomVideoTranscriptList;
use Bristolian\Repo\RoomVideoTranscriptRepo\InMemoryRoomVideoTranscriptRepo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\InMemoryRoomVideoTranscriptRepo
 * @group standard_repo
 */
class InMemoryRoomVideoTranscriptRepoTest extends TestCase
{
    public function test_getTranscripts_returns_empty_list_initially(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $list = $repo->getTranscriptsForRoomVideo('room-video-1');

        $this->assertInstanceOf(RoomVideoTranscriptList::class, $list);
        $this->assertSame([], $list->transcripts);
    }

    public function test_addTranscript_returns_id(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $id = $repo->addTranscript('room-video-1', 'en', 'WEBVTT\n\n00:00.000 --> 00:01.000\nHello');

        $this->assertNotEmpty($id);
    }

    public function test_addTranscript_is_retrievable_by_id(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $id = $repo->addTranscript('room-video-1', 'en', 'WEBVTT content');
        $transcript = $repo->getTranscriptById($id);

        $this->assertInstanceOf(RoomVideoTranscript::class, $transcript);
        $this->assertSame($id, $transcript->id);
        $this->assertSame('room-video-1', $transcript->room_video_id);
        $this->assertSame('en', $transcript->language);
        $this->assertSame('WEBVTT content', $transcript->vtt_content);
        $this->assertSame(1, $transcript->transcript_number);
        $this->assertInstanceOf(\DateTimeInterface::class, $transcript->created_at);
    }

    public function test_addTranscript_auto_increments_transcript_number(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $id1 = $repo->addTranscript('room-video-1', 'en', 'English VTT');
        $id2 = $repo->addTranscript('room-video-1', 'fr', 'French VTT');

        $transcript1 = $repo->getTranscriptById($id1);
        $transcript2 = $repo->getTranscriptById($id2);

        $this->assertSame(1, $transcript1->transcript_number);
        $this->assertSame(2, $transcript2->transcript_number);
    }

    public function test_transcript_numbers_are_independent_per_room_video(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $id1 = $repo->addTranscript('room-video-1', 'en', 'English for video 1');
        $id2 = $repo->addTranscript('room-video-2', 'en', 'English for video 2');

        $transcript1 = $repo->getTranscriptById($id1);
        $transcript2 = $repo->getTranscriptById($id2);

        $this->assertSame(1, $transcript1->transcript_number);
        $this->assertSame(1, $transcript2->transcript_number);
    }

    public function test_getTranscripts_returns_transcripts_ordered_by_number(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $repo->addTranscript('room-video-1', 'en', 'English VTT');
        $repo->addTranscript('room-video-1', 'fr', 'French VTT');
        $repo->addTranscript('room-video-1', 'de', 'German VTT');

        $list = $repo->getTranscriptsForRoomVideo('room-video-1');

        $this->assertCount(3, $list->transcripts);
        $this->assertSame(1, $list->transcripts[0]->transcript_number);
        $this->assertSame(2, $list->transcripts[1]->transcript_number);
        $this->assertSame(3, $list->transcripts[2]->transcript_number);
    }

    public function test_getTranscripts_different_room_videos_are_independent(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $repo->addTranscript('room-video-1', 'en', 'English for video 1');
        $repo->addTranscript('room-video-2', 'en', 'English for video 2');

        $list1 = $repo->getTranscriptsForRoomVideo('room-video-1');
        $list2 = $repo->getTranscriptsForRoomVideo('room-video-2');

        $this->assertCount(1, $list1->transcripts);
        $this->assertCount(1, $list2->transcripts);
        $this->assertSame('English for video 1', $list1->transcripts[0]->vtt_content);
        $this->assertSame('English for video 2', $list2->transcripts[0]->vtt_content);
    }

    public function test_addTranscript_with_null_language(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $id = $repo->addTranscript('room-video-1', null, 'VTT content');
        $transcript = $repo->getTranscriptById($id);

        $this->assertNull($transcript->language);
    }

    public function test_getTranscriptById_throws_for_nonexistent_id(): void
    {
        $repo = new InMemoryRoomVideoTranscriptRepo();

        $this->expectException(ContentNotFoundException::class);
        $repo->getTranscriptById('nonexistent-id');
    }
}
