<?php

declare(strict_types = 1);


use Bristolian\Repo\FileStorageRepo\FileType;

function getAllQueries_5(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `file_storage_info` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `filename` varchar(1024) NOT NULL,
  `filetype` varchar(36) NOT NULL,
  `filestate` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_user UNIQUE (id),
  FOREIGN KEY (user_id) REFERENCES user(id)
                            
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_5(): string
{
    return 'File storage';
}