<?php

namespace Bristolian\Service\ObjectStore;

use Bristolian\Filesystem\RoomFileFilesystem;

/**
 * The standard implementation for storing files that are uploaded to rooms.
 */
class StandardRoomFileObjectStore implements RoomFileObjectStore
{
    public function __construct(private RoomFileFilesystem $roomFileFilesystem)
    {
    }

    public function upload(string $filename, string $contents)
    {
        $this->roomFileFilesystem->write($filename, $contents);
    }
}
