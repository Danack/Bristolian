<?php

declare(strict_types = 1);


function getAllQueries_28(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `meme_text` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `text` varchar(4096) NOT NULL COMMENT 'Text generated, up to 4096 characters',
  `meme_id` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_id UNIQUE (id),
  FOREIGN KEY (meme_id) REFERENCES stored_meme(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_28(): string
{
    return 'Meme text for searching';
}