<?php

declare(strict_types = 1);

function getDescription_5(): string
{
    return 'nicknames';
}

function getAllQueries_5(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `nicknames` (  
  `id` INTEGER NOT NULL AUTO_INCREMENT,
  `user_id` varchar(36) NOT NULL,
  `nickname` varchar(256) NOT NULL,
  `version` integer NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uc_user UNIQUE (user_id, version),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Description and text can be mutated.";

SQL;

    return $sql;
}