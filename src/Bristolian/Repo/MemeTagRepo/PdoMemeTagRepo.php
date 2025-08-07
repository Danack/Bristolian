<?php

namespace Bristolian\Repo\MemeTagRepo;

use Bristolian\Parameters\MemeTagParams;
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
        $sql = <<< SQL
insert into meme_tag (
  id,
  user_id,
  meme_id,
  type,
  text
)
values (
  :id,
  :user_id,
  :meme_id,
  :type,
  :text
)
SQL;

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
     * @return array<int, string>
     * @throws \Exception
     */
    public function getUserTagsForMeme(
        string $user_id,
        string $meme_id
    ): array {
        $sql = <<< SQL
select
  id,
  user_id,
  meme_id,
  type,
  text
from
  meme_tag
where
  user_id = :user_id and
  meme_id = :meme_id
SQL;

        $params = [
            ':user_id' => $user_id,
            ':meme_id' => $meme_id
        ];


        // @phpstan-ignore-next-line
        return $this->pdoSimple->fetchAllAsData($sql, $params);
    }

    public function deleteTagForUser(
        string $user_id,
        string $meme_tag_id
    ): int {
        $sql = <<< SQL

delete from
  meme_tag
where
  user_id = :user_id and
  meme_tag_id = :meme_tag_id
SQL;

        $params = [
            ':user_id' => $user_id,
            ':meme_tag_id' => $meme_tag_id
        ];

        return $this->pdoSimple->execute($sql, $params);
    }
}
