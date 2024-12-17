<?php

declare(strict_types = 1);


function getAllQueries_10(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `link` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'User id is stored for billing purposes.',
  `url` varchar(2048) NOT NULL COMMENT 'The url of the link',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_id UNIQUE (id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";


CREATE TABLE `room_link` (
  `id` varchar(36) NOT NULL,
  `room_id` varchar(36) NOT NULL,
  `link_id` varchar(36) NOT NULL,
  `title` varchar(1024) DEFAULT NULL COMMENT 'A short string to display in a list rather than the URL',
  `description` varchar(12000) DEFAULT NULL COMMENT 'A medium sized piece of text to explain what the file is.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (room_id) REFERENCES room(id),
  FOREIGN KEY (link_id) REFERENCES link(id),
  CONSTRAINT uc_id UNIQUE (id),
  CONSTRAINT uc_room_id_link_id UNIQUE (room_id, link_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should are mutable.";

SQL;

    return $sql;
}

function getDescription_10(): string
{
    return 'Links';
}