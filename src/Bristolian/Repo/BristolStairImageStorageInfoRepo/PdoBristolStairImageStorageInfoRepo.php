<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Database\stored_stair_image_file;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;


class PdoBristolStairImageStorageInfoRepo implements BristolStairImageStorageInfoRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $sql = stored_stair_image_file::INSERT;

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':normalized_name' => $normalized_filename,
            ':original_filename' => $uploadedFile->getOriginalName(),
            ':state' => FileState::INITIAL->value,
            ':size' => $uploadedFile->getSize(),
        ];

        try {
            $this->pdo_simple->insert($sql, $params);
        }
        catch (\PDOException $pdoException) {
            // TODO - technically, this should check the message also.
            if ((int)$pdoException->getCode() === 23000) {
                throw new UserConstraintFailedException(
                    "Failed to insert, user constraint errored.",
                    $pdoException->getCode(),
                    $pdoException
                );
            }

            // Rethrow original exception as it wasn't a failure to insert.
            throw $pdoException;
        }

        return $id;
    }

    public function setUploaded(string $file_storage_id): void
    {
        $sql = <<< SQL
update
  stored_stair_image_file 
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
            throw new \Exception("Failed to update uploaded file.");
        }
    }
}
