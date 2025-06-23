<?php

declare(strict_types = 1);


use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\Repo\FileStorageInfoRepo\FileType;

function getAllQueries_8(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `pdo_simple_test` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `test_string` varchar(36) NOT NULL,
  `test_int` INT NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX index_room_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries can change.";

SQL;

    $sql[] = 'insert into pdo_simple_test (test_string, test_int) values ("first test string", 1)';
    $sql[] = 'insert into pdo_simple_test (test_string, test_int) values ("second test string", 2)';

    return $sql;
}

function getDescription_8(): string
{
    return 'Adding test table for PdoSimple';
}
