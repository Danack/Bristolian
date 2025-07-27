<?php

declare(strict_types = 1);


function getAllQueries_18(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `processor_run_record` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `processor_type` varchar(36) NOT NULL,
  `debug_info` varchar(1024) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  
  INDEX index_status (processor_type, created_at)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_18(): string
{
    return 'processor_run_record';
}