<?php

declare(strict_types = 1);


function getAllQueries_12(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `stored_meme` (
  `id` varchar(36) NOT NULL,
  `normalized_name` varchar(64) NOT NULL COMMENT 'The filename as stored in the object store.',
  `original_filename` varchar(1024) NOT NULL COMMENT 'The filename as uploaded by the user',
  `state` varchar(36) NOT NULL,
  `size` integer NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'User id is stored for billing purposes.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_id UNIQUE (id),
  FOREIGN KEY (user_id) REFERENCES user(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_12(): string
{
    return 'Stored memes';
}