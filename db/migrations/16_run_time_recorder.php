<?php

declare(strict_types = 1);


function getAllQueries_16(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `run_time_recorder` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `task` varchar(256) NOT NULL,
  `status` varchar(36) NOT NULL,
  `start_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` datetime,
  PRIMARY KEY (`id`),
  INDEX index_status (status),
  INDEX index_status_start_time (status, start_time)
 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries are mutable.";

SQL;
    return $sql;
}

function getDescription_16(): string
{
    return 'Add run_time_recorder';
}