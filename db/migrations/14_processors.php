<?php

declare(strict_types = 1);


function getAllQueries_14(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `processor` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `type` varchar(36) NOT NULL,
  `enabled` BOOLEAN NOT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP 
                     ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  CONSTRAINT uc_type UNIQUE (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries are mutable.";

SQL;
    return $sql;
}

function getDescription_14(): string
{
    return 'Add constraint for meme tags';
}