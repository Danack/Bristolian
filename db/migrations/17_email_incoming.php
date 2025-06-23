<?php

declare(strict_types = 1);


function getAllQueries_17(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `email_incoming` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `message_id` varchar(256) NOT NULL,
  `body_plain` BLOB NOT NULL,
  `recipient` varchar(512) NOT NULL,
  `sender` varchar(512) NOT NULL,
  `stripped_text` BLOB NOT NULL,
  `subject` varchar(4096) NOT NULL,
  `provider_variables` varchar(4096) NOT NULL,
  `raw_email` BLOB NOT NULL,
  `status` varchar(36) NOT NULL COMMENT "Whether the email has been processed by our system or not.",
  `retries` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP 
                     ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX index_sender (sender),
  INDEX index_created_at (created_at)   
  
 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries are mutable.";

SQL;
    return $sql;
}

function getDescription_17(): string
{
    return 'Adding  email_incoming';
}