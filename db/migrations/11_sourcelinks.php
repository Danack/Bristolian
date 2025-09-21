<?php

declare(strict_types = 1);

function getAllQueries_11(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `sourcelink` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'User id is stored for billing purposes.',
  `file_id` varchar(36) NOT NULL,
  `highlights_json` BLOB NOT NULL COMMENT 'The pages and rectangles that define the highlighted text.',
  `text` BLOB NOT NULL COMMENT 'The text that was highlighted.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (user_id) REFERENCES user(id),
  FOREIGN KEY (file_id) REFERENCES stored_file(id),
  CONSTRAINT uc_id UNIQUE (id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";
SQL;

$sql[] = <<< SQL
CREATE TABLE `room_sourcelink` (
  `id` varchar(36) NOT NULL,
  `room_id` varchar(36) NOT NULL,
  `sourcelink_id` varchar(36) NOT NULL,
  `title` varchar(1024) DEFAULT NULL COMMENT 'A short string to display in a list',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES room(id),
  FOREIGN KEY (sourcelink_id) REFERENCES sourcelink(id),
  CONSTRAINT uc_id UNIQUE (id),
  CONSTRAINT uc_room_id_link_id UNIQUE (room_id, sourcelink_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should are mutable.";

SQL;

    return $sql;
}

function getDescription_11(): string
{
    return 'Notes';
}