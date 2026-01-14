<?php

namespace Bristolian\Repo\MemeStorageRepo;

use Bristolian\Database\stored_meme;
use Bristolian\Model\Types\Meme;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\MemeTagRepo\MemeTagType;
use Bristolian\Repo\RoomFileObjectInfoRepo\FileState;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

class PdoMemeStorageRepo implements MemeStorageRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }


    public function getMeme(string $id): Meme|null
    {
        $sql = stored_meme::SELECT;
        $sql .= " where id = :id";

        $params = [':id' => $id];

        return $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            Meme::class
        );
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

    /**
     * @return Meme[]
     */
    public function searchMemesForUser(
        string $user_id,
        ?string $query,
        ?string $tag_type
    ): array {
        // If no search criteria, return all memes
        if ($query === null && $tag_type === null) {
            return $this->listMemesForUser($user_id);
        }

        // Always search only user_tag tags - system tags are not visible in search
        $sql = <<< SQL
SELECT DISTINCT
  sm.id,
  sm.normalized_name,
  sm.original_filename,
  sm.state,
  sm.size,
  sm.user_id,
    sm.created_at
FROM
  stored_meme sm
JOIN
  meme_tag mt ON sm.id = mt.meme_id
WHERE
  sm.user_id = :user_id AND
  sm.state = :state AND
  mt.type = :user_tag_type
SQL;

        $params = [
            ':user_id' => $user_id,
            ':state' => FileState::UPLOADED->value,
            ':user_tag_type' => MemeTagType::USER_TAG->value
        ];

        if ($query !== null && $query !== '') {
            $sql .= " AND mt.text LIKE :query";
            $params[':query'] = '%' . $query . '%';
        }

        $memes = $this->pdo_simple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            Meme::class
        );
        return $memes;
    }

    /**
     * @return Meme[]
     */
    public function searchMemesByExactTags(
        string $user_id,
        array $tagTexts
    ): array {
        if (count($tagTexts) === 0) {
            return $this->listMemesForUser($user_id);
        }

        // Create placeholders for tag texts
        $tagPlaceholders = [];
        $params = [
            ':user_id' => $user_id,
            ':state' => FileState::UPLOADED->value,
            ':user_tag_type' => MemeTagType::USER_TAG->value
        ];

        foreach ($tagTexts as $index => $tagText) {
            $placeholder = ':tag_text_' . $index;
            $tagPlaceholders[] = $placeholder;
            $params[$placeholder] = $tagText;
        }

        $tagInClause = implode(', ', $tagPlaceholders);

        // Find memes that have ALL of the specified tags
        // This uses a subquery to count how many of the requested tags each meme has
        $sql = <<< SQL
SELECT DISTINCT
  sm.id,
  sm.normalized_name,
  sm.original_filename,
  sm.state,
  sm.size,
  sm.user_id,
  sm.created_at
FROM
  stored_meme sm
WHERE
  sm.user_id = :user_id AND
  sm.state = :state AND
  (
    SELECT COUNT(DISTINCT mt.text)
    FROM meme_tag mt
    WHERE mt.meme_id = sm.id
      AND mt.type = :user_tag_type
      AND mt.text IN ($tagInClause)
  ) = :tag_count
SQL;

        $params[':tag_count'] = count($tagTexts);

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
