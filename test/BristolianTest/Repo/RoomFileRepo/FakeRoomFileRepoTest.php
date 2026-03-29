<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use VarMap\ArrayVarMap;

/**
 * Tests for FakeRoomFileRepo
 *
 * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
 * @group standard_repo
 */
class FakeRoomFileRepoTest extends RoomFileRepoFixture
{
    /**
     * @return RoomFileRepo
     */
    public function getTestInstance(): RoomFileRepo
    {
        return new FakeRoomFileRepo();
    }

    protected function getValidRoomId(): string
    {
        return 'room_456';
    }

    protected function getValidFileId(): string
    {
        return 'file_123';
    }
    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_constructor(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $this->assertInstanceOf(FakeRoomFileRepo::class, $roomFileRepo);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFilesForRoom_returns_empty_initially(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $files = $roomFileRepo->getFilesForRoom('room_123', RoomContentSearchParams::default());

        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_addFileToRoom_and_getFilesForRoom(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id_1 = 'file_1';
        $file_id_2 = 'file_2';

        // Check room has no files listed
        $files = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());
        $this->assertEmpty($files);

        // Check adding files works
        $roomFileRepo->addFileToRoom($file_id_1, $room_id);
        $roomFileRepo->addFileToRoom($file_id_2, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());
        $this->assertCount(2, $files);

        // Check other room still has no files listed
        $files = $roomFileRepo->getFilesForRoom("some_other_room", RoomContentSearchParams::default());
        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFilesForRoom_returns_stored_files(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());

        $this->assertCount(1, $files);
        $this->assertInstanceOf(\Bristolian\Model\Types\RoomFileInRoom::class, $files[0]);
        $this->assertSame($file_id, $files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_files_in_different_rooms_are_independent(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id_1 = 'room_1';
        $room_id_2 = 'room_2';
        $file_id_1 = 'file_1';
        $file_id_2 = 'file_2';

        $roomFileRepo->addFileToRoom($file_id_1, $room_id_1);
        $roomFileRepo->addFileToRoom($file_id_2, $room_id_2);

        $room1_files = $roomFileRepo->getFilesForRoom($room_id_1, RoomContentSearchParams::default());
        $room2_files = $roomFileRepo->getFilesForRoom($room_id_2, RoomContentSearchParams::default());

        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);
        $this->assertSame($file_id_1, $room1_files[0]->id);
        $this->assertSame($file_id_2, $room2_files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_file(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);

        $fileDetails = $roomFileRepo->getFileDetails($room_id, $file_id);

        $this->assertInstanceOf(RoomFileObjectInfo::class, $fileDetails);
        $this->assertSame($file_id, $fileDetails->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_nonexistent_file(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $fileDetails = $roomFileRepo->getFileDetails('room_123', 'nonexistent_file');

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFileDetails
     */
    public function test_getFileDetails_returns_null_when_file_not_in_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $roomFileRepo->addFileToRoom('file_1', 'room_1');
        $this->assertNull($roomFileRepo->getFileDetails('room_1', 'other_file'));
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_nonexistent_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $fileDetails = $roomFileRepo->getFileDetails('nonexistent_room', 'file_1');

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_file_in_different_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id_1 = 'room_1';
        $room_id_2 = 'room_2';
        $file_id = 'file_1';

        // Add file to room_1
        $roomFileRepo->addFileToRoom($file_id, $room_id_1);

        // Try to get file from room_2
        $fileDetails = $roomFileRepo->getFileDetails($room_id_2, $file_id);

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_same_file_in_multiple_rooms(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id_1 = 'room_1';
        $room_id_2 = 'room_2';
        $file_id = 'file_1';

        // Add same file to both rooms
        $roomFileRepo->addFileToRoom($file_id, $room_id_1);
        $roomFileRepo->addFileToRoom($file_id, $room_id_2);

        $room1_files = $roomFileRepo->getFilesForRoom($room_id_1, RoomContentSearchParams::default());
        $room2_files = $roomFileRepo->getFilesForRoom($room_id_2, RoomContentSearchParams::default());

        // Both rooms should have the file
        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);
        $this->assertSame($file_id, $room1_files[0]->id);
        $this->assertSame($file_id, $room2_files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_stored_file_properties(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());

        $file = $files[0];
        $this->assertInstanceOf(\DateTimeInterface::class, $file->created_at);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_multiple_files_in_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';

        $roomFileRepo->addFileToRoom('file_1', $room_id);
        $roomFileRepo->addFileToRoom('file_2', $room_id);
        $roomFileRepo->addFileToRoom('file_3', $room_id);

        $files = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());

        $this->assertCount(3, $files);
        $this->assertContainsOnlyInstancesOf(\Bristolian\Model\Types\RoomFileInRoom::class, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_matches_getFilesForRoom(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);

        $files_list = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());
        $file_from_list = $files_list[0];

        $file_details = $roomFileRepo->getFileDetails($room_id, $file_id);

        $this->assertInstanceOf(RoomFileObjectInfo::class, $file_details);
        $this->assertInstanceOf(\Bristolian\Model\Types\RoomFileInRoom::class, $file_from_list);
        $this->assertSame($file_id, $file_from_list->id);
        $this->assertSame($file_id, $file_details->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesForRoom
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::filterFilesBySearch
     */
    public function test_getFilesForRoom_filters_by_title(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_123';
        $roomFileRepo->addFileToRoom('file_alpha', $room_id);
        $roomFileRepo->addFileToRoom('match_me_in_name', $room_id);

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['title' => 'match_me']));
        $files = $roomFileRepo->getFilesForRoom($room_id, $search);

        $this->assertCount(1, $files);
        $this->assertSame('match_me_in_name', $files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesForRoom
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::filterFilesBySearch
     */
    public function test_getFilesForRoom_filters_by_created_at_after(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_123';
        $roomFileRepo->addFileToRoom('file_1', $room_id);

        $future = (new \DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d H:i:s');
        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_after' => $future]));
        $files = $roomFileRepo->getFilesForRoom($room_id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesForRoom
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::filterFilesBySearch
     */
    public function test_getFilesForRoom_filters_by_created_at_before(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_123';
        $roomFileRepo->addFileToRoom('file_1', $room_id);

        $past = (new \DateTimeImmutable('now'))->modify('-1 day')->format('Y-m-d H:i:s');
        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_before' => $past]));
        $files = $roomFileRepo->getFilesForRoom($room_id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesForRoom
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::filterFilesBySearch
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::setDocumentTimestampForFileInRoom
     */
    public function test_getFilesForRoom_filters_by_document_timestamp_after(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_123';
        $roomFileRepo->addFileToRoom('file_1', $room_id);
        $roomFileRepo->setDocumentTimestampForFileInRoom($room_id, 'file_1', new \DateTimeImmutable('2024-06-01 12:00:00'));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([
            'document_timestamp_after' => '2024-06-02 00:00:00',
        ]));
        $files = $roomFileRepo->getFilesForRoom($room_id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesForRoom
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::filterFilesBySearch
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::setDocumentTimestampForFileInRoom
     */
    public function test_getFilesForRoom_filters_by_document_timestamp_before(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_123';
        $roomFileRepo->addFileToRoom('file_1', $room_id);
        $roomFileRepo->setDocumentTimestampForFileInRoom($room_id, 'file_1', new \DateTimeImmutable('2024-06-15 12:00:00'));

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([
            'document_timestamp_before' => '2024-06-01 00:00:00',
        ]));
        $files = $roomFileRepo->getFilesForRoom($room_id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::registerFileObjectInfo
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesInRoomByOriginalFilename
     */
    public function test_registerFileObjectInfo_and_getFilesInRoomByOriginalFilename_returns_matching_files(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_1';
        $created = new \DateTimeImmutable('2020-01-01 12:00:00');
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-a',
            'norm.pdf',
            'report.pdf',
            'uploaded',
            100,
            'user-1',
            $created
        ));
        $roomFileRepo->addFileToRoom('file-a', $room_id);

        $matching = $roomFileRepo->getFilesInRoomByOriginalFilename($room_id, 'report.pdf');

        $this->assertCount(1, $matching);
        $this->assertSame('file-a', $matching[0]->id);
        $this->assertSame('report.pdf', $matching[0]->original_filename);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesInRoomByOriginalFilename
     */
    public function test_getFilesInRoomByOriginalFilename_returns_empty_for_unknown_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $matching = $roomFileRepo->getFilesInRoomByOriginalFilename('no_such_room', 'x.pdf');

        $this->assertSame([], $matching);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesInRoomByOriginalFilename
     */
    public function test_getFilesInRoomByOriginalFilename_returns_empty_when_no_original_filename_match(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_1';
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-a',
            'norm.pdf',
            'report.pdf',
            'uploaded',
            100,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->addFileToRoom('file-a', $room_id);

        $matching = $roomFileRepo->getFilesInRoomByOriginalFilename($room_id, 'other.pdf');

        $this->assertSame([], $matching);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesInRoomByOriginalFilename
     */
    public function test_getFilesInRoomByOriginalFilename_returns_newest_first_when_multiple_match(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_1';
        $older = new \DateTimeImmutable('2019-01-01');
        $newer = new \DateTimeImmutable('2021-01-01');
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-old',
            'a.pdf',
            'dup.pdf',
            'uploaded',
            100,
            'user-1',
            $older
        ));
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-new',
            'b.pdf',
            'dup.pdf',
            'uploaded',
            200,
            'user-1',
            $newer
        ));
        $roomFileRepo->addFileToRoom('file-old', $room_id);
        $roomFileRepo->addFileToRoom('file-new', $room_id);

        $matching = $roomFileRepo->getFilesInRoomByOriginalFilename($room_id, 'dup.pdf');

        $this->assertCount(2, $matching);
        $this->assertSame('file-new', $matching[0]->id);
        $this->assertSame('file-old', $matching[1]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesInRoomByOriginalFilename
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::setDocumentTimestampForFileInRoom
     */
    public function test_getFilesInRoomByOriginalFilename_includes_document_timestamp_when_set(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_1';
        $document_timestamp = new \DateTimeImmutable('2020-06-15 12:00:00');
        $roomFileRepo->registerFileObjectInfo(new RoomFileObjectInfo(
            'file-a',
            'norm.pdf',
            'report.pdf',
            'uploaded',
            100,
            'user-1',
            new \DateTimeImmutable()
        ));
        $roomFileRepo->addFileToRoom('file-a', $room_id);
        $roomFileRepo->setDocumentTimestampForFileInRoom($room_id, 'file-a', $document_timestamp);

        $matching = $roomFileRepo->getFilesInRoomByOriginalFilename($room_id, 'report.pdf');

        $this->assertCount(1, $matching);
        $this->assertNotNull($matching[0]->document_timestamp);
        $this->assertSame(
            $document_timestamp->getTimestamp(),
            $matching[0]->document_timestamp->getTimestamp()
        );
    }

    /**
     * Room membership can reference a file id without metadata if internal state is inconsistent; skip those ids.
     *
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::getFilesInRoomByOriginalFilename
     */
    public function test_getFilesInRoomByOriginalFilename_skips_file_ids_without_registered_metadata(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_1';
        $roomFileRepo->addFileToRoom('file_a', $room_id);

        $reflection = new \ReflectionClass($roomFileRepo);
        $files_property = $reflection->getProperty('files');
        $files_property->setAccessible(true);
        $files = $files_property->getValue($roomFileRepo);
        unset($files['file_a']);
        $files_property->setValue($roomFileRepo, $files);

        $matching = $roomFileRepo->getFilesInRoomByOriginalFilename($room_id, 'original_file_a.txt');

        $this->assertSame([], $matching);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::updateRoomFileDetails
     */
    public function test_updateRoomFileDetails_updates_description_note_and_document_timestamp(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $room_id = 'room_123';
        $file_id = 'file_1';
        $roomFileRepo->addFileToRoom($file_id, $room_id);
        $description = 'Desc ' . create_test_uniqid();
        $note = 'Note ' . create_test_uniqid();
        $documentTimestamp = new \DateTimeImmutable('2022-03-15 10:30:00');

        $roomFileRepo->updateRoomFileDetails($room_id, $file_id, $description, $note, $documentTimestamp);

        $files = $roomFileRepo->getFilesForRoom($room_id, RoomContentSearchParams::default());
        $this->assertCount(1, $files);
        $this->assertSame($description, $files[0]->description);
        $this->assertSame($note, $files[0]->note);
        $this->assertNotNull($files[0]->document_timestamp);
        $this->assertSame($documentTimestamp->getTimestamp(), $files[0]->document_timestamp->getTimestamp());
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::updateRoomFileDetails
     */
    public function test_updateRoomFileDetails_throws_when_file_not_in_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('File not found in room');
        $roomFileRepo->updateRoomFileDetails(
            'room_missing',
            'file_missing',
            'd',
            'n',
            new \DateTimeImmutable()
        );
    }
}
