<?php

declare(strict_types = 1);


function getAllQueries_27(): array
{
    $sql = [];

    $sql[] = <<< SQL

CREATE TABLE `bcc_tro_information` (

  `id` INT AUTO_INCREMENT NOT NULL,
  tro_data JSON NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_27(): string
{
    return 'Bcc Tro Information';
}

