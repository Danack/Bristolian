<?php

namespace BristolianTest\Repo\RoomFileRepo;

use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Parameters\TagParams;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Repo\RoomFileTagRepo\PdoRoomFileTagRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Generated\Room;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomFileRepoTest extends RoomFileRepoFixture
{
    use HasTestWorld;
    use TestPlaceholders;

    public function getTestInstance(): RoomFileRepo
    {
        return $this->injector->make(PdoRoomFileRepo::class);
    }

    protected function getValidRoomId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getHousingRoom()->id;
    }

    protected function getValidFileId(): string
    {
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $name = 'fixture_file_' . uniqid() . '.txt';
        $fileId = $this->world()->roomFileObjectInfoRepo()->createRoomFileObjectInfo(
            $userId,
            $name,
            $uploadedFile
        );
        $this->world()->roomFileObjectInfoRepo()->setRoomFileObjectUploaded($fileId);
        return $fileId;
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_addFileToRoom_and_getFilesForRoom(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        // Check room has no files listed
        $files = $roomFileRepo->getFilesForRoom($room->id, RoomContentSearchParams::default());
        $this->assertEmpty($files);

        // Check adding files works
        $roomFileRepo->addFileToRoom($file_id, $room->id);
        $files = $roomFileRepo->getFilesForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(1, $files);
        $this->assertInstanceOf(\Bristolian\Model\Types\RoomFileInRoom::class, $files[0]);

        // Check other room still has no files listed
        $files = $roomFileRepo->getFilesForRoom("some other room", RoomContentSearchParams::default());
        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_getFilesForRoom_returns_empty_for_nonexistent_room(): void
    {
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $files = $roomFileRepo->getFilesForRoom('nonexistent-room-id', RoomContentSearchParams::default());

        $this->assertIsArray($files);
        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_addFileToRoom_multiple_files(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $file_id_1 = $this->createTestFile($user);
        $file_id_2 = $this->createTestFile($user);
        $file_id_3 = $this->createTestFile($user);

        $roomFileRepo->addFileToRoom($file_id_1, $room->id);
        $roomFileRepo->addFileToRoom($file_id_2, $room->id);
        $roomFileRepo->addFileToRoom($file_id_3, $room->id);

        $files = $roomFileRepo->getFilesForRoom($room->id, RoomContentSearchParams::default());

        $this->assertCount(3, $files);
        $this->assertContainsOnlyInstancesOf(\Bristolian\Model\Types\RoomFileInRoom::class, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_files_in_different_rooms(): void
    {
        [$room1, $user] = $this->createTestUserAndRoom();
        [$room2, $user2] = $this->createTestUserAndRoom();
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $file_id_1 = $this->createTestFile($user);
        $file_id_2 = $this->createTestFile($user);

        $roomFileRepo->addFileToRoom($file_id_1, $room1->id);
        $roomFileRepo->addFileToRoom($file_id_2, $room2->id);

        $room1_files = $roomFileRepo->getFilesForRoom($room1->id, RoomContentSearchParams::default());
        $room2_files = $roomFileRepo->getFilesForRoom($room2->id, RoomContentSearchParams::default());

        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);

        // Verify correct file in each room
        $this->assertSame($file_id_1, $room1_files[0]->id);
        $this->assertSame($file_id_2, $room2_files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_stored_file_properties(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $roomFileRepo->addFileToRoom($file_id, $room->id);
        $files = $roomFileRepo->getFilesForRoom($room->id, RoomContentSearchParams::default());

        $file = $files[0];
        $this->assertIsInt($file->size);
        $this->assertInstanceOf(\DateTimeInterface::class, $file->created_at);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_getFileDetails_returns_file(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $roomFileRepo->addFileToRoom($file_id, $room->id);

        $fileDetails = $roomFileRepo->getFileDetails($room->id, $file_id);

        $this->assertInstanceOf(RoomFileObjectInfo::class, $fileDetails);
        $this->assertSame($file_id, $fileDetails->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_nonexistent_file(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $fileDetails = $roomFileRepo->getFileDetails($room->id, 'nonexistent-file-id');

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_file_in_different_room(): void
    {
        [$room1, $user] = $this->createTestUserAndRoom();
        [$room2, $user2] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        // Add file to room1
        $roomFileRepo->addFileToRoom($file_id, $room1->id);

        // Try to get file details for room2
        $fileDetails = $roomFileRepo->getFileDetails($room2->id, $file_id);

        // Should be null because file is not in room2
        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_getFileDetails_properties_match_getFilesForRoom(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $roomFileRepo->addFileToRoom($file_id, $room->id);

        $files_list = $roomFileRepo->getFilesForRoom($room->id, RoomContentSearchParams::default());
        $file_from_list = $files_list[0];

        $file_details = $roomFileRepo->getFileDetails($room->id, $file_id);

        // Verify properties match between both methods
        $this->assertSame($file_from_list->id, $file_details->id);
        $this->assertSame($file_from_list->normalized_name, $file_details->normalized_name);
        $this->assertSame($file_from_list->original_filename, $file_details->original_filename);
        $this->assertSame($file_from_list->state, $file_details->state);
        $this->assertSame($file_from_list->size, $file_details->size);
        $this->assertSame($file_from_list->user_id, $file_details->user_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_same_file_can_be_in_multiple_rooms(): void
    {
        [$room1, $user] = $this->createTestUserAndRoom();
        [$room2, $user2] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        // Add same file to both rooms
        $roomFileRepo->addFileToRoom($file_id, $room1->id);
        $roomFileRepo->addFileToRoom($file_id, $room2->id);

        $room1_files = $roomFileRepo->getFilesForRoom($room1->id, RoomContentSearchParams::default());
        $room2_files = $roomFileRepo->getFilesForRoom($room2->id, RoomContentSearchParams::default());

        // Both rooms should have the file
        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);
        $this->assertSame($file_id, $room1_files[0]->id);
        $this->assertSame($file_id, $room2_files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::getFilesForRoom
     */
    public function test_getFilesForRoom_filters_by_title(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $path = __DIR__ . '/../../../sample.pdf';
        $uploadedFile = new UploadedFile($path, (int) filesize($path), 'report_unique_title_slug.pdf', 0);
        $fileId = $this->world()->roomFileObjectInfoRepo()->createRoomFileObjectInfo(
            $user->getUserId(),
            'r_uniq.pdf',
            $uploadedFile
        );
        $this->world()->roomFileObjectInfoRepo()->setRoomFileObjectUploaded($fileId);
        $roomFileRepo->addFileToRoom($fileId, $room->id);

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['title' => 'unique_title_slug']));
        $files = $roomFileRepo->getFilesForRoom($room->id, $search);

        $this->assertCount(1, $files);
        $this->assertSame($fileId, $files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::getFilesForRoom
     */
    public function test_getFilesForRoom_filters_by_created_at_after(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $fileId = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $roomFileRepo->addFileToRoom($fileId, $room->id);

        $future = (new \DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d H:i:s');
        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_after' => $future]));
        $files = $roomFileRepo->getFilesForRoom($room->id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::getFilesForRoom
     */
    public function test_getFilesForRoom_filters_by_created_at_before(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $fileId = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $roomFileRepo->addFileToRoom($fileId, $room->id);

        $past = (new \DateTimeImmutable('now'))->modify('-1 day')->format('Y-m-d H:i:s');
        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['created_at_before' => $past]));
        $files = $roomFileRepo->getFilesForRoom($room->id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::getFilesForRoom
     */
    public function test_getFilesForRoom_filters_by_document_timestamp_after(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $fileId = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $roomFileRepo->addFileToRoom($fileId, $room->id);

        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->execute(
            'UPDATE room_file SET document_timestamp = :ts WHERE room_id = :room_id AND stored_file_id = :stored_file_id',
            [
                ':ts' => '2024-06-01 12:00:00',
                ':room_id' => $room->id,
                ':stored_file_id' => $fileId,
            ]
        );

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([
            'document_timestamp_after' => '2024-06-02 00:00:00',
        ]));
        $files = $roomFileRepo->getFilesForRoom($room->id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::getFilesForRoom
     */
    public function test_getFilesForRoom_filters_by_document_timestamp_before(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $fileId = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $roomFileRepo->addFileToRoom($fileId, $room->id);

        $pdoSimple = $this->injector->make(PdoSimple::class);
        $pdoSimple->execute(
            'UPDATE room_file SET document_timestamp = :ts WHERE room_id = :room_id AND stored_file_id = :stored_file_id',
            [
                ':ts' => '2024-06-15 12:00:00',
                ':room_id' => $room->id,
                ':stored_file_id' => $fileId,
            ]
        );

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap([
            'document_timestamp_before' => '2024-06-01 00:00:00',
        ]));
        $files = $roomFileRepo->getFilesForRoom($room->id, $search);

        $this->assertCount(0, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::getFilesForRoom
     */
    public function test_getFilesForRoom_filters_by_tag_ids(): void
    {
        [$room, $user] = $this->createTestUserAndRoom();
        $fileId = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);
        $roomFileRepo->addFileToRoom($fileId, $room->id);

        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'file-tag-' . create_test_uniqid(),
            'description' => 'Tag for file filter test',
        ])));
        $roomFileTagRepo = $this->injector->make(PdoRoomFileTagRepo::class);
        $roomFileTagRepo->setTagsForRoomFile($room->id, $fileId, [$tag->tag_id]);

        $search = RoomContentSearchParams::createFromVarMap(new ArrayVarMap(['tag_ids' => $tag->tag_id]));
        $files = $roomFileRepo->getFilesForRoom($room->id, $search);

        $this->assertCount(1, $files);
        $this->assertSame($fileId, $files[0]->id);
    }

}
