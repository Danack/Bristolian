<?php

namespace Bristolian\Repo\FileStorageRepo;

use Bristolian\Repo\FileStorageRepo\FileType;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Ramsey\Uuid\Uuid;
use Bristolian\Repo\FileStorageRepo\FileState;
use Bristolian\PdoSimple;
use Bristolian\Model\Meme;

class PdoFileStorageInfoRepo implements FileStorageInfoRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    /**
     * @return Meme[]
     */
    public function listMemesForUser(string $user_id): array
    {
        $sql = <<< SQL
select 
  id,
  user_id, 
  filename,
  filetype,
  filestate
from
  file_storage_info
where
  user_id = :user_id and 
  filetype = :filetype and
  filestate = :filestate
SQL;

        $params = [
            ':user_id' => $user_id,
            ':filetype' => FileType::Meme->value,
            ':filestate' => FileState::UPLOADED->value
        ];

        $memes = $this->pdo_simple->fetchAllAsObject(
            $sql,
            $params,
            Meme::class
        );
        return $memes;
    }

    public function createEntry(
        string $user_id,
        string $filename,
        FileType $filetype
    ): string {

        $sql = <<< SQL
insert into file_storage_info (
  id,
  user_id, 
  filename,
  filetype,
  filestate
)
values (
  :id,
  :user_id, 
  :filename,
  :filetype,
  :filestate
)
SQL;
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            'id' => $id,
            'user_id' => $user_id,
            'filename' => $filename,
            'filetype' => $filetype->value,
            'filestate' => FileState::INITIAL->value,
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
  file_storage_info 
set
  filestate = :filestate
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
