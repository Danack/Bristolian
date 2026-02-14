<?php

namespace BristolianTest\Repo\RoomFileRepo;

use Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Generated\Room;

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
        $files = $roomFileRepo->getFilesForRoom($room->id);
        $this->assertEmpty($files);

        // Check adding files works
        $roomFileRepo->addFileToRoom($file_id, $room->id);
        $files = $roomFileRepo->getFilesForRoom($room->id);
        $this->assertCount(1, $files);
        $this->assertInstanceOf(RoomFileObjectInfo::class, $files[0]);

        // Check other room still has no files listed
        $files = $roomFileRepo->getFilesForRoom("some other room");
        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function test_getFilesForRoom_returns_empty_for_nonexistent_room(): void
    {
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        $files = $roomFileRepo->getFilesForRoom('nonexistent-room-id');

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

        $files = $roomFileRepo->getFilesForRoom($room->id);

        $this->assertCount(3, $files);
        $this->assertContainsOnlyInstancesOf(RoomFileObjectInfo::class, $files);
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

        $room1_files = $roomFileRepo->getFilesForRoom($room1->id);
        $room2_files = $roomFileRepo->getFilesForRoom($room2->id);

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
        $files = $roomFileRepo->getFilesForRoom($room->id);

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

        $files_list = $roomFileRepo->getFilesForRoom($room->id);
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

        $room1_files = $roomFileRepo->getFilesForRoom($room1->id);
        $room2_files = $roomFileRepo->getFilesForRoom($room2->id);

        // Both rooms should have the file
        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);
        $this->assertSame($file_id, $room1_files[0]->id);
        $this->assertSame($file_id, $room2_files[0]->id);
    }
}
