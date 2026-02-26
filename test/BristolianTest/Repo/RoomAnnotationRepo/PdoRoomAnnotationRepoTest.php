<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationRepo;

use Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\Support\HasTestWorld;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomAnnotationRepoTest extends RoomAnnotationRepoFixture
{
    use HasTestWorld;

    private ?string $fixtureRoomId1 = null;
    private ?string $fixtureRoomId2 = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixtureRoomId1 = null;
        $this->fixtureRoomId2 = null;
    }

    public function getTestInstance(): RoomAnnotationRepo
    {
        return $this->injector->make(PdoRoomAnnotationRepo::class);
    }

    protected function getValidUserId(): string
    {
        $this->ensureStandardSetup();
        return $this->standardTestData()->getTestingUserId();
    }

    protected function getValidRoomId(): string
    {
        if ($this->fixtureRoomId1 !== null) {
            return $this->fixtureRoomId1;
        }
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->standardTestData()->ensureRoom(
            'RoomAnnotationFixture1_' . uniqid(),
            'Fixture room for RoomAnnotationRepo tests (no existing links)',
            $userId
        );
        $this->fixtureRoomId1 = $room->id;
        return $this->fixtureRoomId1;
    }

    protected function getValidRoomId2(): string
    {
        if ($this->fixtureRoomId2 !== null) {
            return $this->fixtureRoomId2;
        }
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->standardTestData()->ensureRoom(
            'RoomAnnotationFixture2_' . uniqid(),
            'Second fixture room for RoomAnnotationRepo tests',
            $userId
        );
        $this->fixtureRoomId2 = $room->id;
        return $this->fixtureRoomId2;
    }

    protected function getValidFileId(): string
    {
        return $this->createValidFileId();
    }

    protected function getValidFileId2(): string
    {
        return $this->createValidFileId();
    }

    private function createValidFileId(): string
    {
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $name = 'annotation_fixture_' . uniqid() . '.txt';
        $fileId = $this->world()->roomFileObjectInfoRepo()->createRoomFileObjectInfo(
            $userId,
            $name,
            $uploadedFile
        );
        $this->world()->roomFileObjectInfoRepo()->setRoomFileObjectUploaded($fileId);
        return $fileId;
    }
}
