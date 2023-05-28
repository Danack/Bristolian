<?php

declare(strict_types = 1);


function getAllQueries_1(): array
{
    $sql = [];

    $sql[] = <<< SQL

CREATE TABLE `user` (
  `id` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_user UNIQUE (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

//  CONSTRAINT `user_links_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)

    return $sql;
}