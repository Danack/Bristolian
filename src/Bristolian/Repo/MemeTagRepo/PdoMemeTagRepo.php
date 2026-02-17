<?php

namespace Bristolian\Repo\MemeTagRepo;

use Bristolian\Database\meme_tag;
use Bristolian\Model\Generated\MemeTag;
use Bristolian\Parameters\MemeTagParams;
use Bristolian\Parameters\MemeTagUpdateParams;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoMemeTagRepo implements MemeTagRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }

    public function addTagForMeme(
        string        $user_id,
        MemeTagParams $memeTagParam,
    ): void {
        $sql = meme_tag::INSERT;

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
            ':user_id' => $user_id,
            ':meme_id' => $memeTagParam->meme_id,
            ':type' => $memeTagParam->type,
            ':text' => $memeTagParam->text,
        ];

        $this->pdoSimple->insert($sql, $params);
    }

    /**
     * @param string $user_id
     * @param string $meme_id
     * @return MemeTag[]
     */
    public function getUserTagsForMeme(
        string $user_id,
        string $meme_id
    ): array {
        // Return all tags for a meme that belongs to the user (not just tags created by the user)
        // This allows displaying system tags in the future, but only user_tag tags will be editable
        $sql = <<< SQL
select
    mt.id,
    mt.user_id,
    mt.meme_id,
    mt.type,
    mt.text,
    mt.created_at
from
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
where
  sm.user_id = :user_id and
  mt.meme_id = :meme_id
SQL;

        $params = [
            ':user_id' => $user_id,
            ':meme_id' => $meme_id
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor($sql, $params, MemeTag::class);
    }

    public function updateTagForUser(
        string $user_id,
        MemeTagUpdateParams $memeTagUpdateParams,
    ): int {
        // Only allow updating user_tag tags that belong to memes owned by the user
        // Force type to 'user_tag' to prevent changing tag type
        $sql = <<< SQL
update
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
set
  mt.type = :user_tag_type,
  mt.text = :text
where
  sm.user_id = :user_id and
  mt.id = :meme_tag_id and
  mt.type = :user_tag_type_check
SQL;

        $params = [
            ':user_id' => $user_id,
            ':meme_tag_id' => $memeTagUpdateParams->meme_tag_id,
            ':user_tag_type' => MemeTagType::USER_TAG->value,
            ':user_tag_type_check' => MemeTagType::USER_TAG->value,
            ':text' => $memeTagUpdateParams->text,
        ];

        return $this->pdoSimple->execute($sql, $params);
    }

    public function deleteTagForUser(
        string $user_id,
        string $meme_tag_id
    ): int {
        // Only allow deleting user_tag tags that belong to memes owned by the user
        $sql = <<< SQL
delete mt from
  meme_tag mt
inner join stored_meme sm on mt.meme_id = sm.id
where
  sm.user_id = :user_id and
  mt.id = :meme_tag_id and
  mt.type = :user_tag_type
SQL;

        $params = [
            ':user_id' => $user_id,
            ':meme_tag_id' => $meme_tag_id,
            ':user_tag_type' => MemeTagType::USER_TAG->value
        ];

        return $this->pdoSimple->execute($sql, $params);
    }

    /**
     * @return array<array{text: string, count: int}>
     */
    public function getMostCommonTags(
        string $user_id,
        int $limit
    ): array {
        $sql = <<< SQL
SELECT 
    mt.text,
    COUNT(*) as count
FROM
    meme_tag mt
INNER JOIN stored_meme sm ON mt.meme_id = sm.id
WHERE
    sm.user_id = :user_id AND
    mt.type = :user_tag_type AND
    sm.state = :state
GROUP BY
    mt.text
ORDER BY
    count DESC,
    mt.text ASC
LIMIT :limit
SQL;

        $params = [
            ':user_id' => $user_id,
            ':user_tag_type' => MemeTagType::USER_TAG->value,
            ':state' => \Bristolian\Repo\RoomFileObjectInfoRepo\FileState::UPLOADED->value,
            ':limit' => $limit
        ];

        $results = $this->pdoSimple->fetchAllAsData($sql, $params);
        
        // Convert to expected format
        $tags = [];
        foreach ($results as $row) {
            $tags[] = [
                'text' => $row['text'],
                'count' => (int)$row['count']
            ];
        }
        
        return $tags;
    }

    /**
     * @return array<array{text: string, count: int}>
     */
    public function getMostCommonTagsForMemes(
        string $user_id,
        array $meme_ids,
        int $limit
    ): array {
        if (count($meme_ids) === 0) {
            return [];
        }

        // Create placeholders for IN clause
        $placeholders = [];
        $params = [
            ':user_id' => $user_id,
            ':user_tag_type' => MemeTagType::USER_TAG->value,
            ':state' => \Bristolian\Repo\RoomFileObjectInfoRepo\FileState::UPLOADED->value,
            ':limit' => $limit
        ];

        foreach ($meme_ids as $index => $meme_id) {
            $placeholder = ':meme_id_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $meme_id;
        }

        $inClause = implode(', ', $placeholders);

        $sql = <<< SQL
SELECT 
    mt.text,
    COUNT(*) as count
FROM
    meme_tag mt
INNER JOIN stored_meme sm ON mt.meme_id = sm.id
WHERE
    sm.user_id = :user_id AND
    mt.type = :user_tag_type AND
    sm.state = :state AND
    sm.id IN ($inClause)
GROUP BY
    mt.text
ORDER BY
    count DESC,
    mt.text ASC
LIMIT :limit
SQL;

        $results = $this->pdoSimple->fetchAllAsData($sql, $params);
        
        // Convert to expected format
        $tags = [];
        foreach ($results as $row) {
            $tags[] = [
                'text' => $row['text'],
                'count' => (int)$row['count']
            ];
        }
        
        return $tags;
    }
}
