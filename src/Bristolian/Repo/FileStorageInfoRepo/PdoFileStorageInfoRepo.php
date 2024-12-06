<?php

namespace Bristolian\Repo\FileStorageInfoRepo;

use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;
use Bristolian\PdoSimple;
use Bristolian\Database\stored_file;

class PdoFileStorageInfoRepo implements FileStorageInfoRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

//    /**
//     * @return Meme[]
//     */
//    public function listMemesForUser(string $user_id): array
//    {
//        $sql = file_storage_info::SELECT . <<< SQL
//where
//  user_id = :user_id and
//
//  filestate = :filestate
//SQL;
//        echo "This is broken. Memes for users needs a need table setting up to store that info.";
//        exit(0);
//        $params = [
//            ':user_id' => $user_id,
//            ':filetype' => FileType::Meme->value,
////            ':filestate' => FileState::UPLOADED->value
//        ];
//
//        $memes = $this->pdo_simple->fetchAllAsObject(
//            $sql,
//            $params,
//            Meme::class
//        );
//        return $memes;
//    }


    public function storeFileInfo(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $sql = stored_file::INSERT;

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
  stored_file 
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
