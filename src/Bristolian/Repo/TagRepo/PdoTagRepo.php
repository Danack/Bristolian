<?php

namespace Bristolian\Repo\TagRepo;

use Bristolian\Model\Types\Tag;
use Bristolian\Parameters\TagParams;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoTagRepo implements TagRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function createTag(TagParams $tagParam): Tag
    {
        $uuid = Uuid::uuid7();
        $userSQL = <<< SQL
insert into tags (
  tag_id,
  text,
  description
)
values (
  :tag_id,
  :text,
  :description
)
SQL;

        $params = [
            ':tag_id' => $uuid->toString(),
            ':text' => $tagParam->text,
            ':description' => $tagParam->description
        ];

        $this->pdo_simple->insert($userSQL, $params);

        return Tag::fromParam($uuid->toString(), $tagParam);
    }




    /**
     * @return \Bristolian\Model\Types\Tag[]
     */
    public function getAllTags(): array
    {
        $sql = <<< SQL
select 
  tag_id,
  text,
  description
from
  tags
SQL;

        return $this->pdo_simple->fetchAllAsObject($sql, [], Tag::class);
    }
}
