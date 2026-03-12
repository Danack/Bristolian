<?php

declare(strict_types = 1);

namespace BristolianTest\AppController;

use Bristolian\AppController\Rooms;
use Bristolian\Exception\BristolianException;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\RoomFileFilesystem;
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
use Bristolian\Response\FetchTranscriptErrorResponse;
use Bristolian\Response\FetchTranscriptSuccessResponse;
use Bristolian\Response\GetTranscriptResponse;
use Bristolian\Response\GetTranscriptsResponse;
use Bristolian\Response\RoomFileUploadErrorResponse;
use Bristolian\Response\RoomFileUploadSuccessResponse;
use Bristolian\Response\StoredFileErrorResponse;
use Bristolian\Response\StreamingResponse;
use Bristolian\Response\SuccessResponse;
use Bristolian\Response\Typed\GetRoomsAnnotationsResponse;
use Bristolian\Response\Typed\GetRoomsFileAnnotationsResponse;
use Bristolian\Response\Typed\GetRoomsFilesResponse;
use Bristolian\Response\Typed\GetRoomsLinksResponse;
use Bristolian\Response\Typed\GetRoomsTagsResponse;
use Bristolian\Response\Typed\GetRoomsVideosResponse;
use Bristolian\Service\RoomFileStorage\FakeRoomFileStorage;
use Bristolian\Service\RoomFileStorage\RoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError as RoomFileUploadError;
use Bristolian\Service\YouTube\FakeYouTubeTranscriptFetcher;
use Bristolian\Service\YouTube\TranscriptFetcher;
use Bristolian\SiteHtml\AssetLinkEmitter;
use Bristolian\Response\IframeHtmlResponse;
use Bristolian\Service\RequestNonce;
use Bristolian\UploadedFiles\FakeUploadedFiles;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\UploadedFiles\UploadedFiles;
use BristolianTest\BaseTestCase;
use League\Flysystem\Local\LocalFilesystemAdapter;
use VarMap\ArrayVarMap;

/** TranscriptFetcher that throws for testing error path */
final class ThrowingTranscriptFetcher implements TranscriptFetcher
{
    public function fetchAsVtt(string $youtubeVideoId): array
    {
        throw new \RuntimeException('Transcript fetch failed');
    }
}

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
     * @covers \Bristolian\AppController\Rooms::handleFileUpload
     */
    public function test_handleFileUpload_returns_error_response_when_upload_handler_returns_response(): void
    {
        $uploadedFiles = new FakeUploadedFiles([]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);
        $storage = new FakeRoomFileStorage('unused');
        $this->injector->alias(RoomFileStorage::class, FakeRoomFileStorage::class);
        $this->injector->share($storage);

        $result = $this->injector->execute([Rooms::class, 'handleFileUpload']);

        $this->assertInstanceOf(\SlimDispatcher\Response\StubResponse::class, $result);
        $this->assertSame(500, $result->getStatus());
    }

    /**
     * @covers \Bristolian\AppController\Rooms::handleFileUpload
     */
    public function test_handleFileUpload_returns_RoomFileUploadErrorResponse_when_storage_returns_error(): void
    {
        $storage = new FakeRoomFileStorage(RoomFileUploadError::unsupportedFileType());
        $this->injector->alias(RoomFileStorage::class, FakeRoomFileStorage::class);
        $this->injector->share($storage);

        $tmpFile = tmpfile();
        $this->assertNotFalse($tmpFile);
        $meta = stream_get_meta_data($tmpFile);
        $uploadedFile = new UploadedFile($meta['uri'], 10, 'test.pdf', 0);
        $uploadedFiles = new FakeUploadedFiles([Rooms::ROOM_FILE_UPLOAD_FORM_NAME => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);

        $result = $this->injector->execute([Rooms::class, 'handleFileUpload']);

        $this->assertInstanceOf(RoomFileUploadErrorResponse::class, $result);
        $this->assertSame(400, $result->getStatus());
        $this->assertStringContainsString('error', $result->getBody());
        fclose($tmpFile);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::handleFileUpload
     */
    public function test_handleFileUpload_returns_RoomFileUploadSuccessResponse_on_success(): void
    {
        $storage = new FakeRoomFileStorage('uploaded-file-id-123');
        $this->injector->alias(RoomFileStorage::class, FakeRoomFileStorage::class);
        $this->injector->share($storage);

        $tmpFile = tmpfile();
        $this->assertNotFalse($tmpFile);
        $meta = stream_get_meta_data($tmpFile);
        $uploadedFile = new UploadedFile($meta['uri'], 10, 'test.pdf', 0);
        $uploadedFiles = new FakeUploadedFiles([Rooms::ROOM_FILE_UPLOAD_FORM_NAME => $uploadedFile]);
        $this->injector->alias(UploadedFiles::class, FakeUploadedFiles::class);
        $this->injector->share($uploadedFiles);

        $result = $this->injector->execute([Rooms::class, 'handleFileUpload']);

        $this->assertInstanceOf(RoomFileUploadSuccessResponse::class, $result);
        $this->assertSame(200, $result->getStatus());
        $body = json_decode($result->getBody(), true);
        $this->assertSame('success', $body['result']);
        $this->assertSame('uploaded-file-id-123', $body['file_id']);
        fclose($tmpFile);
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
     * @covers \Bristolian\AppController\Rooms::getFiles
     * @covers \Bristolian\AppController\Rooms::resolveTagIdsToTags
     */
    public function test_getFiles_with_files_and_tags_resolves_tags(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('file-with-tag', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'important',
            'description' => 'Important file',
        ])));

        $roomFileTagRepo = $this->injector->make(FakeRoomFileTagRepo::class);
        $roomFileTagRepo->setTagsForRoomFile($this->roomId, $fileId, [$tag->tag_id]);

        $result = $this->injector->execute([Rooms::class, 'getFiles']);

        $this->assertInstanceOf(GetRoomsFilesResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertCount(1, $data['data']['files']);
        $this->assertCount(1, $data['data']['files'][0]['tags']);
        $this->assertSame('important', $data['data']['files'][0]['tags'][0]['text']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::getLinks
     * @covers \Bristolian\AppController\Rooms::resolveTagIdsToTags
     */
    public function test_getLinks_with_links_and_tags_resolves_tags(): void
    {
        $linkParam = LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com/doc',
            'title' => 'Example Doc',
            'description' => 'A document',
        ]));
        $roomLinkRepo = $this->injector->make(FakeRoomLinkRepo::class);
        $roomLinkId = $roomLinkRepo->addLinkToRoomFromParam('test-user-id-001', $this->roomId, $linkParam);

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'reference',
            'description' => 'Reference link',
        ])));

        $roomLinkTagRepo = $this->injector->make(FakeRoomLinkTagRepo::class);
        $roomLinkTagRepo->setTagsForRoomLink($roomLinkId, [$tag->tag_id]);

        $result = $this->injector->execute([Rooms::class, 'getLinks']);

        $this->assertInstanceOf(GetRoomsLinksResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertCount(1, $data['data']['links']);
        $this->assertCount(1, $data['data']['links'][0]['tags']);
        $this->assertSame('reference', $data['data']['links'][0]['tags'][0]['text']);
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
            'title' => 'Test Video with at least 16 chars',
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
            'title' => 'A clip title that is at least 16 chars',
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
     * @covers \Bristolian\AppController\Rooms::fetchTranscript
     */
    public function test_fetchTranscript_returns_error_when_fetcher_throws(): void
    {
        $videoRepo = $this->injector->make(InMemoryVideoRepo::class);
        $videoId = $videoRepo->create('test-user-id-001', 'dQw4w9WgXcQ');
        $roomVideoRepo = $this->injector->make(InMemoryRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Video', 'Desc');

        $this->injector->alias(TranscriptFetcher::class, ThrowingTranscriptFetcher::class);
        $this->injector->share(new ThrowingTranscriptFetcher());
        $this->injector->defineParam('room_video_id', $roomVideo->id);

        $result = $this->injector->execute([Rooms::class, 'fetchTranscript']);

        $this->assertInstanceOf(FetchTranscriptErrorResponse::class, $result);
        $this->assertStringContainsString('Transcript fetch failed', $result->getBody());
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
     * @covers \Bristolian\AppController\Rooms::getAnnotations
     * @covers \Bristolian\AppController\Rooms::resolveTagIdsToTags
     */
    public function test_getAnnotations_with_annotations_and_tags_returns_annotations_with_resolved_tags(): void
    {
        $annotationRepo = $this->injector->make(FakeRoomAnnotationRepo::class);
        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Annotation text',
        ]));
        $roomAnnotationId = $annotationRepo->addAnnotation(
            'test-user-id-001',
            $this->roomId,
            'file-for-annotations',
            $annotationParam
        );

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'annotation-tag',
            'description' => 'Tag for annotation',
        ])));

        $roomAnnotationTagRepo = $this->injector->make(FakeRoomAnnotationTagRepo::class);
        $roomAnnotationTagRepo->setTagsForRoomAnnotation($roomAnnotationId, [$tag->tag_id]);

        $result = $this->injector->execute([Rooms::class, 'getAnnotations']);

        $this->assertInstanceOf(GetRoomsAnnotationsResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertCount(1, $data['data']['annotations']);
        $this->assertCount(1, $data['data']['annotations'][0]['tags']);
        $this->assertSame('annotation-tag', $data['data']['annotations'][0]['tags'][0]['text']);
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
     * @covers \Bristolian\AppController\Rooms::getAnnotationsForFile
     * @covers \Bristolian\AppController\Rooms::resolveTagIdsToTags
     */
    public function test_getAnnotationsForFile_with_annotations_and_tags_returns_annotations_with_resolved_tags(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('storage-id-annot', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $annotationRepo = $this->injector->make(FakeRoomAnnotationRepo::class);
        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '{"highlights": []}',
            'text' => 'File annotation text',
        ]));
        $roomAnnotationId = $annotationRepo->addAnnotation(
            'test-user-id-001',
            $this->roomId,
            $fileId,
            $annotationParam
        );

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'file-annotation-tag',
            'description' => 'Tag for file annotation',
        ])));

        $roomAnnotationTagRepo = $this->injector->make(FakeRoomAnnotationTagRepo::class);
        $roomAnnotationTagRepo->setTagsForRoomAnnotation($roomAnnotationId, [$tag->tag_id]);

        $this->injector->defineParam('file_id', $fileId);
        $result = $this->injector->execute([Rooms::class, 'getAnnotationsForFile']);

        $this->assertInstanceOf(GetRoomsFileAnnotationsResponse::class, $result);
        $data = json_decode($result->getBody(), true);
        $this->assertCount(1, $data['data']['annotations']);
        $this->assertCount(1, $data['data']['annotations'][0]['tags']);
        $this->assertSame('file-annotation-tag', $data['data']['annotations'][0]['tags'][0]['text']);
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

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'video-tag',
            'description' => 'Tag for video',
        ])));

        $jsonInput = new FakeJsonInput(['tag_ids' => [$tag->tag_id]]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_video_id', $roomVideo->id);

        $result = $this->injector->execute([Rooms::class, 'setVideoTags']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setVideoTags
     */
    public function test_setVideoTags_throws_when_room_video_not_in_room(): void
    {
        $jsonInput = new FakeJsonInput(['tag_ids' => []]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_video_id', 'nonexistent-room-video-id');

        $this->expectException(ContentNotFoundException::class);

        $this->injector->execute([Rooms::class, 'setVideoTags']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::updateVideo
     */
    public function test_updateVideo(): void
    {
        $videoRepo = $this->injector->make(InMemoryVideoRepo::class);
        $videoId = $videoRepo->create('test-user-id-001', 'abc123');
        $roomVideoRepo = $this->injector->make(InMemoryRoomVideoRepo::class);
        $roomVideo = $roomVideoRepo->addVideo($this->roomId, $videoId, 'Original Title of enough chars', 'Original description');

        $new_title = 'Updated Title that is at least 16 chars';

        $jsonInput = new FakeJsonInput([
            'title' => $new_title,
            'description' => 'Updated description',
        ]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_video_id', $roomVideo->id);

        $result = $this->injector->execute([Rooms::class, 'updateVideo']);
        $this->assertInstanceOf(SuccessResponse::class, $result);

        $fetched = $roomVideoRepo->getRoomVideoForRoom($this->roomId, $roomVideo->id);
        $this->assertSame($new_title, $fetched->title);
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

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'annotation-tag',
            'description' => 'Tag for annotation',
        ])));

        $jsonInput = new FakeJsonInput(['tag_ids' => [$tag->tag_id]]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_annotation_id', $annotationId);

        $result = $this->injector->execute([Rooms::class, 'setAnnotationTags']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setAnnotationTags
     */
    public function test_setAnnotationTags_throws_when_annotation_not_found(): void
    {
        $jsonInput = new FakeJsonInput(['tag_ids' => []]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_annotation_id', 'nonexistent-annotation-id');

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Annotation not found');

        $this->injector->execute([Rooms::class, 'setAnnotationTags']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setFileTags
     */
    public function test_setFileTags_throws_when_file_not_in_room(): void
    {
        $jsonInput = new FakeJsonInput(['tag_ids' => []]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('file_id', 'nonexistent-file-id');

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('File not found in room');

        $this->injector->execute([Rooms::class, 'setFileTags']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setFileTags
     */
    public function test_setFileTags_success(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('file-for-tags', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-for-file',
            'description' => 'Tag for file',
        ])));

        $jsonInput = new FakeJsonInput(['tag_ids' => [$tag->tag_id]]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'setFileTags']);
        $this->assertInstanceOf(SuccessResponse::class, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setLinkTags
     */
    public function test_setLinkTags_throws_when_link_not_found(): void
    {
        $jsonInput = new FakeJsonInput(['tag_ids' => []]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_link_id', 'nonexistent-room-link-id');

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Link not found in room');

        $this->injector->execute([Rooms::class, 'setLinkTags']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::setLinkTags
     */
    public function test_setLinkTags_success(): void
    {
        $linkParam = LinkParam::createFromVarMap(new ArrayVarMap([
            'url' => 'https://example.com',
            'title' => 'Link for tags',
            'description' => 'Description for link',
        ]));
        $roomLinkRepo = $this->injector->make(FakeRoomLinkRepo::class);
        $roomLinkId = $roomLinkRepo->addLinkToRoomFromParam('test-user-id-001', $this->roomId, $linkParam);

        $roomTagRepo = $this->injector->make(FakeRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($this->roomId, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'link-tag',
            'description' => 'Link tag desc',
        ])));

        $jsonInput = new FakeJsonInput(['tag_ids' => [$tag->tag_id]]);
        $this->injector->alias(JsonInput::class, FakeJsonInput::class);
        $this->injector->share($jsonInput);
        $this->injector->defineParam('room_link_id', $roomLinkId);

        $result = $this->injector->execute([Rooms::class, 'setLinkTags']);
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

        // highlights_json must be a JSON array (frontend sends JSON.stringify(highlights)); min length 16
        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '[{"page":1,"left":0,"top":0,"right":100,"bottom":10}]',
            'text' => 'Annotation text',
        ]));
        $this->injector->share($annotationParam);
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'handleAddAnnotation']);
        $this->assertNotNull($result);
        $body = json_decode($result->getBody(), true);
        $this->assertSame('success', $body['result'] ?? null);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('room_annotation_id', $body['data']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::handleAddAnnotation
     */
    public function test_handleAddAnnotation_returns_error_when_highlights_invalid(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('some-storage-id', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'This is a longer source title that meets the minimum length requirement',
            'highlights_json' => '[{"invalid": "object"}]',
            'text' => 'Annotation text',
        ]));
        $this->injector->share($annotationParam);
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'handleAddAnnotation']);

        $this->assertNotNull($result);
        $body = $result->getBody();
        $this->assertStringContainsString('"success": false', $body);
        $this->assertStringContainsString('"errors"', $body);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::annotate_file
     * @covers \Bristolian\AppController\Rooms::render_annotate_file
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
        $this->assertStringContainsString('annotation_panel', $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::annotate_file
     * @covers \Bristolian\AppController\Rooms::render_annotate_file
     */
    public function test_annotate_file_throws_when_file_not_in_room(): void
    {
        $this->injector->defineParam('file_id', 'nonexistent-file-id');

        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('not found');

        $this->injector->execute([Rooms::class, 'annotate_file']);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::viewAnnotation
     * @covers \Bristolian\AppController\Rooms::render_annotate_file
     */
    public function test_viewAnnotation(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('some-storage-id', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;
        $this->injector->defineParam('file_id', $fileId);
        $selectedAnnotationId = 'some-annotation-id';
        $this->injector->defineParam('annotation_id', $selectedAnnotationId);

        $result = $this->injector->execute([Rooms::class, 'viewAnnotation']);
        $this->assertIsString($result);
        $this->assertStringContainsString('Test Room', $result);
        $this->assertStringContainsString($selectedAnnotationId, $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::iframe_show_file
     */
    public function test_iframe_show_file_returns_string_when_file_not_found(): void
    {
        $this->injector->share(new RequestNonce());
        $this->injector->share(new AssetLinkEmitter(new \Bristolian\Config\HardCodedAssetLinkConfig(false, 'test')));
        $this->injector->defineParam('file_id', 'nonexistent-file-id');

        $result = $this->injector->execute([Rooms::class, 'iframe_show_file']);

        $this->assertIsString($result);
        $this->assertSame('File not found.', $result);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::iframe_show_file
     */
    public function test_iframe_show_file_returns_response_when_file_found(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('iframe-file', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $this->injector->share(new RequestNonce());
        $this->injector->share(new AssetLinkEmitter(new \Bristolian\Config\HardCodedAssetLinkConfig(false, 'test')));
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'iframe_show_file']);

        $this->assertInstanceOf(IframeHtmlResponse::class, $result);
        $this->assertStringContainsString('pdf_view.js', $result->getBody());
    }

    /**
     * @covers \Bristolian\AppController\Rooms::serveFileForRoom
     */
    public function test_serveFileForRoom_throws_when_file_not_found(): void
    {
        $roomTempDir = sys_get_temp_dir() . '/bristolian_room_fs_' . uniqid();
        $cacheTempDir = sys_get_temp_dir() . '/bristolian_room_cache_' . uniqid();
        mkdir($roomTempDir, 0755, true);
        mkdir($cacheTempDir, 0755, true);
        $this->injector->share(new RoomFileFilesystem(new LocalFilesystemAdapter($roomTempDir)));
        $this->injector->share(new LocalCacheFilesystem(new LocalFilesystemAdapter($cacheTempDir), $cacheTempDir));
        $this->injector->defineParam('file_id', 'nonexistent-file-id');

        try {
            $this->expectException(BristolianException::class);
            $this->expectExceptionMessage('File not found');

            $this->injector->execute([Rooms::class, 'serveFileForRoom']);
        } finally {
            rmdir($roomTempDir);
            rmdir($cacheTempDir);
        }
    }

    /**
     * @covers \Bristolian\AppController\Rooms::serveFileForRoom
     */
    public function test_serveFileForRoom_returns_StoredFileErrorResponse_when_file_unreadable(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('unreadable-file', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;

        $roomTempDir = sys_get_temp_dir() . '/bristolian_room_fs_' . uniqid();
        $cacheTempDir = sys_get_temp_dir() . '/bristolian_room_cache_' . uniqid();
        mkdir($roomTempDir, 0755, true);
        mkdir($cacheTempDir, 0755, true);
        $roomFilesystem = new RoomFileFilesystem(new LocalFilesystemAdapter($roomTempDir));
        $localCacheFilesystem = new LocalCacheFilesystem(
            new LocalFilesystemAdapter($cacheTempDir),
            $cacheTempDir
        );
        $this->injector->share($roomFilesystem);
        $this->injector->share($localCacheFilesystem);
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'serveFileForRoom']);

        $this->assertInstanceOf(StoredFileErrorResponse::class, $result);
        $this->assertSame(500, $result->getStatus());
        $this->assertStringContainsString('normalized_unreadable-file.txt', $result->getBody());

        rmdir($roomTempDir);
        rmdir($cacheTempDir);
    }

    /**
     * @covers \Bristolian\AppController\Rooms::serveFileForRoom
     */
    public function test_serveFileForRoom_returns_StreamingResponse_when_file_available(): void
    {
        $roomFileRepo = $this->injector->make(FakeRoomFileRepo::class);
        $roomFileRepo->addFileToRoom('serve-test', $this->roomId);
        $files = $roomFileRepo->getFilesForRoom($this->roomId);
        $fileId = $files[0]->id;
        $normalizedName = 'normalized_serve-test.txt';

        $roomTempDir = sys_get_temp_dir() . '/bristolian_room_fs_' . uniqid();
        $cacheTempDir = sys_get_temp_dir() . '/bristolian_room_cache_' . uniqid();
        mkdir($roomTempDir, 0755, true);
        mkdir($cacheTempDir, 0755, true);
        file_put_contents($roomTempDir . '/' . $normalizedName, 'file content for streaming');

        $roomFilesystem = new RoomFileFilesystem(new LocalFilesystemAdapter($roomTempDir));
        $localCacheFilesystem = new LocalCacheFilesystem(
            new LocalFilesystemAdapter($cacheTempDir),
            $cacheTempDir
        );
        $this->injector->share($roomFilesystem);
        $this->injector->share($localCacheFilesystem);
        $this->injector->defineParam('file_id', $fileId);

        $result = $this->injector->execute([Rooms::class, 'serveFileForRoom']);

        $this->assertInstanceOf(StreamingResponse::class, $result);
        $this->assertSame(200, $result->getStatusCode());

        unlink($roomTempDir . '/' . $normalizedName);
        rmdir($roomTempDir);
        $cachedPath = $cacheTempDir . '/' . $normalizedName;
        if (file_exists($cachedPath)) {
            unlink($cachedPath);
        }
        rmdir($cacheTempDir);
    }

}
