<?php

namespace BristolianTest\Service\RoomFileStorage;

use Bristolian\Filesystem\RoomFileFilesystem;
use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use Bristolian\Service\RoomFileStorage\StandardRoomFileStorage;
use BristolianTest\BaseTestCase;
use Bristolian\Service\FileStorageProcessor\FakeWorksFileStorageProcessor;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;

/**
 * @coversNothing
 */
class StandardRoomFileStorageTest extends BaseTestCase
{

    public function testWorks()
    {
        $this->markTestSkipped("foo bar");
//        $fileStorageInfoRepo = FileStorageInfoRepo ,
//        $roomFileFilesystem = RoomFileFilesystem ,
//        $roomFileRepo = RoomFileRepo
//        $room_file_storage = $this->injector->make(StandardRoomFileStorage::class);
//        $fileStorageRepo = new FakeWorksFileStorageProcessor();
//        $roomFileRepo = new FakeRoomFileRepo();

//        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter(
//        // Determine root directory
//            __DIR__.'/../../../temp/'
//        );
//        $roomFileFilesystem = new RoomFileFilesystem($adapter);

//        $fileStorage = new StandardRoomFileStorage(
//            $fileStorageRepo,
//            $roomFileFilesystem,
//            $roomFileRepo
//        );
//
//
//        $fileStorage->storeFileForRoomAndUser()
    }
}
