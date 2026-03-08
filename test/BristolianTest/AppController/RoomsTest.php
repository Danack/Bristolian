<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Rooms;
use Bristolian\JsonInput\FakeJsonInput;
use Bristolian\JsonInput\JsonInput;
use Bristolian\Parameters\AnnotationParam;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\FakeRoomAnnotationTagRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo;
use Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo;
use Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use Bristolian\Repo\RoomLinkTagRepo\FakeRoomLinkTagRepo;
use Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo;
use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;
use Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use Bristolian\Repo\RoomVideoRepo\InMemoryRoomVideoRepo;
use Bristolian\Repo\RoomVideoRepo\RoomVideoRepo;
use Bristolian\Repo\RoomVideoTagRepo\InMemoryRoomVideoTagRepo;
use Bristolian\Repo\RoomVideoTagRepo\RoomVideoTagRepo;
use Bristolian\Repo\RoomVideoTranscriptRepo\InMemoryRoomVideoTranscriptRepo;
use Bristolian\Repo\RoomVideoTranscriptRepo\RoomVideoTranscriptRepo;
use Bristolian\Repo\VideoRepo\InMemoryVideoRepo;
use Bristolian\Repo\VideoRepo\VideoRepo;
use Bristolian\Response\CreateClipResponse;
use Bristolian\Response\EndpointAccessedViaGetResponse;
use Bristolian\Response\FetchTranscriptSuccessResponse;
use Bristolian\Response\GetTranscriptResponse;
use Bristolian\Response\GetTranscriptsResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetRoomsAnnotationsResponse;
use Bristolian\Response\Typed\GetRoomsFileAnnotationsResponse;
use Bristolian\Response\Typed\GetRoomsFilesResponse;
use Bristolian\Response\Typed\GetRoomsLinksResponse;
use Bristolian\Response\Typed\GetRoomsTagsResponse;
use Bristolian\Response\Typed\GetRoomsVideosResponse;
use Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher;
use Bristolian\Service\YouTube\TranscriptFetcher;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class RoomsTest extends BaseTestCase
{
    private string $roomId;

    public function setup(): void
    {
        parent::setup();
        $this->setupAppControllerFakes();
        $this->setupFakeUserSession();

        $roomRepo = $this->injector->make(FakeRoomRepo::class);
        $room = $roomRepo->createRoom('test-user-id-001', 'Test Room', 'A room for testing');
        $this->roomId = $room->id;
        $this->injector->defineParam('room_id', $this->roomId);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::index
     */
    public function test_index(): void
    {
        $result = $this->injector->execute([Rooms::class, 'index']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Test Room', $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::handleFileUpload_get
     */
    public function test_handleFileUpload_get(): void
    {
        $result = $this->injector->execute([Rooms::class, 'handleFileUpload_get']);
        $this->assertInstanceOf(EndpointAccessedViaGetResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getFiles
     */
    public function test_getFiles(): void
    {
        $result = $this->injector->execute([Rooms::class, 'getFiles']);
        $this->assertInstanceOf(GetRoomsFilesResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getLinks
     */
    public function test_getLinks(): void
    {
        $result = $this->injector->execute([Rooms::class, 'getLinks']);
        $this->assertInstanceOf(GetRoomsLinksResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getVideos
     */
    public function test_getVideos(): void
    {
        $result = $this->injector->execute([Rooms::class, 'getVideos']);
        $this->assertInstanceOf(GetRoomsVideosResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::addVideo
     */
    public function test_addVideo(): void
    {
        $jsonInput = new FakeJsonInput([
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'title' => 'Test Video',
            'description' => 'A test video',
        ]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);

        $result = $this->injector->execute([Rooms::class, 'addVideo']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::createClip
     */
    public function test_createClip(): void
    {
        $videoRepo = $this->injector->make(InMemoryVideoRepo::class);
        $videoId = $videoRepo->create('test-user-id-001', 'dQw4w9WgXcQ');
        $roomVideoRepo = $this->injector->make(InMemoryRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Source Video', 'Original');

        $jsonInput = new FakeJsonInput([
            'room_video_id' => $roomVideo->id,
            'start_seconds' => 10,
            'end_seconds' => 60,
            'title' => 'A clip',
            'description' => 'Clip from source',
        ]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);

        $result = $this->injector->execute([Rooms::class, 'createClip']);
        $this->assertInstanceOf(CreateClipResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getTranscripts
     */
    public function test_getTranscripts(): void
    {
        $this->injector->defineParam('room_video_id', 'fake-room-video-id');
        $result = $this->injector->execute([Rooms::class, 'getTranscripts']);
        $this->assertInstanceOf(GetTranscriptsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getTranscript
     */
    public function test_getTranscript(): void
    {
        $transcriptRepo = $this->injector->make(InMemoryRoomVideoTranscriptRepo::class);
        $transcriptId = $transcriptRepo->addTranscript('room-video-1', 'en', 'WEBVTT\n\nHello');
        $this->injector->defineParam('room_video_id', 'room-video-1');
        $this->injector->defineParam('transcript_id', $transcriptId);

        $result = $this->injector->execute([Rooms::class, 'getTranscript']);
        $this->assertInstanceOf(GetTranscriptResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::fetchTranscript
     */
    public function test_fetchTranscript(): void
    {
        $videoRepo = $this->injector->make(InMemoryVideoRepo::class);
        $videoId = $videoRepo->create('test-user-id-001', 'dQw4w9WgXcQ');
        $roomVideoRepo = $this->injector->make(InMemoryRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Video', 'Desc');

        $fakeFetcher = $this->injector->make(FakeYouTubeTranscriptFetcher::class);
        $fakeFetcher->addTranscript('dQw4w9WgXcQ', "WEBVTT\n\n00:00.000 --> 00:01.000\nHello", 'en');

        $this->injector->defineParam('room_video_id', $roomVideo->id);

        $result = $this->injector->execute([Rooms::class, 'fetchTranscript']);
        $this->assertInstanceOf(FetchTranscriptSuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getTags
     */
    public function test_getTags(): void
    {
        $result = $this->injector->execute([Rooms::class, 'getTags']);
        $this->assertInstanceOf(GetRoomsTagsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::addTag
     */
    public function test_addTag(): void
    {
        $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'test-tag',
            'description' => 'A test tag',
        ]));
        $this->injector->share($tagParam);

        $result = $this->injector->execute([Rooms::class, 'addTag']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getAnnotations
     */
    public function test_getAnnotations(): void
    {
        $result = $this->injector->execute([Rooms::class, 'getAnnotations']);
        $this->assertInstanceOf(GetRoomsAnnotationsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getAnnotationsForFile
     */
    public function test_getAnnotationsForFile(): void
    {
        $this->injector->defineParam('file_id', 'fake-file-id');
        $result = $this->injector->execute([Rooms::class, 'getAnnotationsForFile']);
        $this->assertInstanceOf(GetRoomsFileAnnotationsResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::showRoom
     */
    public function test_showRoom(): void
    {
        $result = $this->injector->execute([Rooms::class, 'showRoom']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Test Room', $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::showRoom
     */
    public function test_showRoom_not_found(): void
    {
        $this->injector->defineParam('room_id', 'nonexistent-room-id');
        $result = $this->injector->execute([Rooms::class, 'showRoom']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Room not found', $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::addLink
     */
    public function test_addLink(): void
    {
        $linkParam = LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://www.example.com',
            'title' => 'Example Link Title',
            'description' => 'An example link description',
        ]));
        $this->injector->share($linkParam);

        $result = $this->injector->execute([Rooms::class, 'addLink']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setVideoTags
     */
    public function test_setVideoTags(): void
    {
        $videoRepo = $this->injector->make(InMemoryVideoRepo::class);
        $videoId = $videoRepo->create('test-user-id-001', 'abc123');
        $roomVideoRepo = $this->injector->make(InMemoryRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Video', null);

        $jsonInput = new FakeJsonInput(['tag_ids' => []]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_video_id', $roomVideo->id);

        $result = $this->injector->execute([Rooms::class, 'setVideoTags']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::updateVideo
     */
    public function test_updateVideo(): void
    {
        $videoRepo = $this->injector->make(InMemoryVideoRepo::class);
        $videoId = $videoRepo->create('test-user-id-001', 'abc123');
        $roomVideoRepo = $this->injector->make(InMemoryRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Original Title', 'Original description');

        $jsonInput = new FakeJsonInput([
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_video_id', $roomVideo->id);

        $result = $this->injector->execute([Rooms::class, 'updateVideo']);
        $this->assertInstanceOf(SuccessResponse::class, $result);

        $fetched = $roomVideoRepo->getRoomVideoForRoom($this->roomId, $roomVideo->id);
        $this->assertSame('Updated Title', $fetched->title);
        $this->assertSame('Updated description', $fetched->description);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setAnnotationTags
     */
    public function test_setAnnotationTags(): void
    {
        $annotationRepo = $this->injector->make(FakeRoomAnnotationRepo::class);
        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Some annotation text',
        ]));
        $annotationId = $annotationRepo->addAnnotation(
            'test-user-id-001',
            $this->roomId,
            'some-file-id',
            $annotationParam
        );

        $jsonInput = new FakeJsonInput(['tag_ids' => []]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_annotation_id', $annotationId);

        $result = $this->injector->execute([Rooms::class, 'setAnnotationTags']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::handleAddAnnotation
     */
    public function test_handleAddAnnotation(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('some-storage-id', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Annotation text',
        ]));
        $this->injector->share($annotationParam);
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'handleAddAnnotation']);
        $this->assertNotNull($result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::annotate_file
     */
    public function test_annotate_file(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('some-storage-id', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'annotate_file']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Test Room', $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::viewAnnotation
     */
    public function test_viewAnnotation(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('some-storage-id', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;
        $this->injector->defineParam('file_id', $fileId);
        $this->injector->defineParam('annotation_id', 'some-annotation-id');

        $result = $this->injector->execute([Rooms::class, 'viewAnnotation']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Test Room', $result);
    }
}
