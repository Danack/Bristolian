<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomVideoTranscriptRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\Model\Types\RoomVideoTranscriptList;
use Bristolian\Repo\RoomVideoRepo\PdoRoomVideoRepo;
use Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo;
use Bristolian\Repo\RoomVideoTranscriptRepo\RoomVideoTranscriptRepo;
use Bristolian\Repo\VideoRepo\PdoVideoRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Support\HasTestWorld;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomVideoTranscriptRepoTest extends BaseTestCase
{
    use HasTestWorld;

    private ?string $roomVideoId = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->world()->roomRepo()->createRoom(
            $userId,
            'T' . time() . '_' . random_int(100, 999),
            'Transcript tests'
        );
        $videoRepo = $this->injector->make(PdoVideoRepo::class);
        $videoId = $videoRepo->create($userId, 'dQw4w9WgXcQ');
        $roomVideoRepo = $this->injector->make(PdoRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($room->id, $videoId, 'Video', null);
        $this->roomVideoId = $roomVideo->id;
    }

    private function getRepo(): RoomVideoTranscriptRepo
    {
        return $this->injector->make(PdoRoomVideoTranscriptRepo::class);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::__construct
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::getTranscriptsForRoomVideo
     */
    public function test_getTranscriptsForRoomVideo_returns_empty_list_before_add(): void
    {
        $repo = $this->getRepo();
        assert($this->roomVideoId !== null);
        $list = $repo->getTranscriptsForRoomVideo($this->roomVideoId);
        $this->assertInstanceOf(RoomVideoTranscriptList::class, $list);
        $this->assertSame([], $list->transcripts);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::addTranscript
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::getTranscriptsForRoomVideo
     */
    public function test_addTranscript_and_getTranscriptsForRoomVideo(): void
    {
        $repo = $this->getRepo();
        assert($this->roomVideoId !== null);
        $id = $repo->addTranscript($this->roomVideoId, 'en', "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\nHello");
        $this->assertNotEmpty($id);
        $list = $repo->getTranscriptsForRoomVideo($this->roomVideoId);
        $this->assertCount(1, $list->transcripts);
        $this->assertInstanceOf(RoomVideoTranscript::class, $list->transcripts[0]);
        $this->assertSame($id, $list->transcripts[0]->id);
        $this->assertSame('en', $list->transcripts[0]->language);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::addTranscript
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::getTranscriptById
     */
    public function test_getTranscriptById_returns_transcript(): void
    {
        $repo = $this->getRepo();
        assert($this->roomVideoId !== null);
        $id = $repo->addTranscript($this->roomVideoId, null, "WEBVTT\n\n00:00:00.000 --> 00:00:02.000\nHi");
        $transcript = $repo->getTranscriptById($id);
        $this->assertInstanceOf(RoomVideoTranscript::class, $transcript);
        $this->assertSame($id, $transcript->id);
        $this->assertSame($this->roomVideoId, $transcript->room_video_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomVideoTranscriptRepo\PdoRoomVideoTranscriptRepo::getTranscriptById
     */
    public function test_getTranscriptById_throws_for_nonexistent_id(): void
    {
        $repo = $this->getRepo();
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('not found');
        $repo->getTranscriptById('00000000-0000-0000-0000-000000000000');
    }
}
