<?php

declare(strict_types = 1);


function getAllQueries_15(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `email_send_queue` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `recipient` varchar(1024) NOT NULL,
  `subject` varchar(1024) NOT NULL,
  `body` blob NOT NULL,
  `status` varchar(36) NOT NULL,
  `retries` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP 
                     ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX index_status (status)
 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries are mutable.";

SQL;
    return $sql;
}

function getDescription_15(): string
{
    return 'Add constraint for meme tags';
}