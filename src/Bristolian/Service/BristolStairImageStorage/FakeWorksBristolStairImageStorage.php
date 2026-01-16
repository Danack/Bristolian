<?php

namespace Bristolian\Service\BristolStairImageStorage;

use Bristolian\Parameters\BristolStairsGpsParams;
use Bristolian\UploadedFiles\UploadedFile;
use Bristolian\Model\Generated\BristolStairInfo;
use Ramsey\Uuid\Uuid;

class FakeWorksBristolStairImageStorage implements BristolStairImageStorage
{
    private int $count = 0;

    /**
     * @param string $user_id
     * @param UploadedFile $uploadedFile
     * @param string[] $allowedExtensions
     * @return BristolStairInfo|UploadError
     */
    public function storeFileForUser(
        string $user_id,
        UploadedFile $uploadedFile,
        array $allowedExtensions,
        BristolStairsGpsParams $gpsParams
    ): BristolStairInfo|UploadError {
        $uuid = Uuid::uuid7();
        $normalized_filename = $uuid->toString() . ".pdf";

        $uuid = Uuid::uuid7();
        $fileStorageId = $uuid->toString();

        $id = $this->count;
        $this->count += 1;

        // Return a fake BristolStairInfo object for testing
        return new BristolStairInfo(
            id: $id,
            description: "Fake stair info for testing",
            latitude: 51.4536491, // Bristol centre
            longitude: -2.5913353,
            stored_stair_image_file_id: $fileStorageId,
            steps: 10,
            is_deleted: 0,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable()
        );
    }
}
