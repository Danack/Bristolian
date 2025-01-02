<?php

namespace Bristolian\Repo\MemeStorageRepo;

use Bristolian\Database\stored_file;
use Bristolian\Database\stored_meme;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;
use Bristolian\Repo\FileStorageInfoRepo\FileState;
use Bristolian\Model\Meme;

class PdoMemeStorageRepo implements MemeStorageRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    /**
     * @return Meme[]
     */
    public function listMemesForUser(string $user_id): array
    {
        $sql = stored_meme::SELECT . <<< SQL
where
  user_id = :user_id and
  state = :state
SQL;

        $params = [
            ':user_id' => $user_id,
            ':state' => FileState::UPLOADED->value
        ];

        $memes = $this->pdo_simple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            Meme::class
        );
        return $memes;
    }



    public function storeMeme(
        string $user_id,
        string $normalized_filename,
        UploadedFile $uploadedFile,
    ): string {

        $sql = stored_meme::INSERT;

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':normalized_name' => $normalized_filename,
            ':original_filename' => $uploadedFile->getOriginalName(),
            ':state' => MemeFileState::INITIAL->value,
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
  stored_meme 
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
