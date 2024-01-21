<?php

declare(strict_types = 1);


use Bristolian\DataType\BasicString;
use Bristolian\Repo\FileStorageRepo\FileType;

function getAllQueries_7(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `room` (
  `id` varchar(36) NOT NULL,
  `owner_user_id` varchar(36) NOT NULL,
  `name` varchar(36)  NOT NULL,
  `purpose` varchar(4096) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_id UNIQUE (id),
  FOREIGN KEY fk_user_id (owner_user_id) REFERENCES user(id),
  INDEX index_room_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries can change.";

SQL;

    return $sql;
}

function getDescription_7(): string
{
    return 'Rooms';
}