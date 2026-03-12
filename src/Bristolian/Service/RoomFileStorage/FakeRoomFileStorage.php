<?php

declare(strict_types=1);

namespace Bristolian\Service\RoomFileStorage;

use Bristolian\UploadedFiles\UploadedFile;

/**
 * Fake RoomFileStorage for tests. Returns a fixed file id or error.
 */
final class FakeRoomFileStorage implements RoomFileStorage
{
    public function __construct(
        private string|UploadError $result
    ) {
    }

    public function storeFileForRoomAndUser(
        string $user_id,
        string $room_id,
        UploadedFile $uploadedFile
    ): string|UploadError {
        return $this->result;
    }
}
