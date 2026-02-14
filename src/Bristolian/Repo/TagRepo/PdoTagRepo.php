<?php

namespace Bristolian\Repo\TagRepo;

use Bristolian\Database\tag as TagTable;
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
        $params = [
            ':tag_id' => $uuid->toString(),
            ':description' => $tagParam->description,
            ':text' => $tagParam->text,
        ];

        $this->pdo_simple->insert(TagTable::INSERT, $params);

        return Tag::fromParam($uuid->toString(), $tagParam);
    }

    /**
     * @return \Bristolian\Model\Types\Tag[]
     */
    public function getAllTags(): array
    {
        $rows = $this->pdo_simple->fetchAllAsData(TagTable::SELECT, []);
        return array_map(fn(array $row): Tag => Tag::fromRow($row), $rows);
    }
}
