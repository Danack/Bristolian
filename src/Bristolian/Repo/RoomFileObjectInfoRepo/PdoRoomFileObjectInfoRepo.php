<?php

namespace Bristolian\Repo\RoomFileObjectInfoRepo;

use Bristolian\Database\room_file_object_info;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Service\UuidGenerator\UuidGenerator;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * Stores information about a file in the local database.
 * The actual file will be stored in an object store.
 */
class PdoRoomFileObjectInfoRepo implements RoomFileObjectInfoRepo
{
    public function __construct(
        private PdoSimple $pdo_simple,
        private UuidGenerator $uuidGenerator
    ) {
    }

    public function createRoomFileObjectInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $sql = room_file_object_info::INSERT;

        $id = $this->uuidGenerator->generate();

        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':normalized_name' => $normalized_filename,
            ':original_filename' => $uploadedFile->getOriginalName(),
            ':state' => FileState::INITIAL->value,
            ':size' => $uploadedFile->getSize(),
        ];

        // TODO - move this into pdo_simple
        try {
            $this->pdo_simple->insert($sql, $params);
        } catch (PdoSimpleWithPreviousException $e) {
            $pdoException = $e->getPreviousPdoException();
            if ((int)$pdoException->getCode() === 23000) {
                throw new UserConstraintFailedException(
                    "Failed to insert, user constraint errored.",
                    $pdoException->getCode(),
                    $pdoException
                );
            }
            // @codeCoverageIgnoreStart
            throw $e;
            // @codeCoverageIgnoreEnd
        }

        return $id;
    }

    public function setRoomFileObjectUploaded(string $file_storage_id): void
    {
        $sql = <<< SQL
update
  room_file_object_info 
set
  state = :filestate
where
  id = :id
SQL;
        $params = [
            ':filestate' => FileState::UPLOADED->value,
            ':id' => $file_storage_id
        ];

        $rows_affected = $this->pdo_simple->execute($sql, $params);
        if ($rows_affected !== 1) {
            // maybe do something
        }
    }
}
