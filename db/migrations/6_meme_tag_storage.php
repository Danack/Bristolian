<?php

declare(strict_types = 1);


use Bristolian\DataType\BasicString;
use Bristolian\Repo\FileStorageInfoRepo\FileType;

function getAllQueries_6(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `meme_tag` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `meme_id` varchar(36) NOT NULL,
  `type` varchar(36)  NOT NULL,
  `text` varchar(4096) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_id UNIQUE (id),
  FOREIGN KEY fk_user_id (user_id) REFERENCES user(id),
  INDEX index_meme_id (meme_id),
  INDEX index_user_id (meme_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_6(): string
{
    return 'Meme tags';
}