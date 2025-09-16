<?php

declare(strict_types = 1);


function getAllQueries_19(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `stored_stair_image_file` (
  `id` varchar(36) NOT NULL,
  `normalized_name` varchar(64) NOT NULL COMMENT 'The filename as stored in the object store.',
  `original_filename` varchar(1024) NOT NULL COMMENT 'The filename as uploaded by the user',
  `state` varchar(36) NOT NULL,
  `size` integer NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'User id is stored for billing purposes.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_id UNIQUE (id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";
SQL;

    $sql[] = <<< SQL
CREATE TABLE `bristol_stair_info` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `description` varchar(1024) DEFAULT NULL COMMENT 'A description of the steps',
  `latitude` FLOAT DEFAULT NULL COMMENT 'Where they are. We allow null to simplify uploading.',
  `longitude`  FLOAT DEFAULT NULL COMMENT 'Where they are. We allow null to simplify uploading.',
  `stored_stair_image_file_id` varchar(36) NOT NULL,
  `steps` integer NOT NULL COMMENT 'The count of steps.',
  `is_deleted` BOOLEAN NOT NULL COMMENT 'There\'s going to be duplicates..',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP 
                     ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  FOREIGN KEY (stored_stair_image_file_id) REFERENCES stored_stair_image_file(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries can be edited.";

SQL;

    return $sql;
}

function getDescription_19(): string
{
    return 'Stored stair image files and information';
}