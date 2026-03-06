<?php

namespace Bristolian\Repo\BristolStairImageStorageInfoRepo;

use Bristolian\Database\stair_image_object_info;
use Bristolian\Model\Generated\StairImageObjectInfo as BristolStairImageFile;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\PdoSimple\PdoSimpleWithPreviousException;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\Service\UuidGenerator\UuidGenerator;
use Bristolian\UploadedFiles\UploadedFile;

class PdoBristolStairImageStorageInfoRepo implements BristolStairImageStorageInfoRepo
{
    public function __construct(
        private PdoSimple $pdo_simple,
        private UuidGenerator $uuidGenerator
    ) {
    }

    public function getByNormalizedName(string $normalized_name): BristolStairImageFile|null
    {
        $sql = stair_image_object_info::SELECT;
        $sql .= " WHERE normalized_name = :normalized_name";

        return $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [':normalized_name' => $normalized_name],
            BristolStairImageFile::class
        );
    }

    public function getById(string $bristol_stairs_image_id): BristolStairImageFile|null
    {
        $sql = stair_image_object_info::SELECT;
        $sql .= " WHERE id = :id";

        return $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [':id' => $bristol_stairs_image_id],
            BristolStairImageFile::class
        );
    }

    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $sql = stair_image_object_info::INSERT;

        $id = $this->uuidGenerator->generate();

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
        } catch (PdoSimpleWithPreviousException $e) {
            $pdoException = $e->getPreviousPdoException();
            if ((int)$pdoException->getCode() === 23000) {
                throw new UserConstraintFailedException(
                    "Failed to insert, user constraint errored.",
                    $pdoException->getCode(),
                    $pdoException
                );
            }
            throw $e;
        }

        return $id;
    }

    public function setUploaded(string $file_storage_id): void
    {
        $sql = <<< SQL
update
  stair_image_object_info 
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
