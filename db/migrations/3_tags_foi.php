<?php

declare(strict_types = 1);


function getDescription_3(): string
{
    return 'tags foi_requests';
}

function getAllQueries_3(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `tag` (
  `tag_id` varchar(36) NOT NULL,
  `text` varchar(2048) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Description and text can be mutated.";
SQL;

$sql[] = <<< SQL
CREATE TABLE `foi_requests` (
  `foi_request_id` varchar(36) NOT NULL,
  `text` varchar(2048) NOT NULL,
  `url` varchar(2048) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`foi_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Description and text can be mutated.";

SQL;




    return $sql;
}