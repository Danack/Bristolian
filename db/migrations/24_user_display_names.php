<?php

declare(strict_types = 1);


function getAllQueries_24(): array
{
    $sql = [];

    $sql[] = <<< SQL

CREATE TABLE `user_display_name` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'The user this display name belongs to',
  `display_name` varchar(255) NOT NULL COMMENT 'The display name/nickname for the user',
  `version` INT NOT NULL COMMENT 'Version number - increments with each change. Latest version is current.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this display name was set',
  
  PRIMARY KEY (`id`),
  FOREIGN KEY (user_id) REFERENCES user(id),
  UNIQUE KEY unique_user_version (user_id, version),
  INDEX idx_user_version (user_id, version DESC)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries are versioned - each change creates a new row. Version increments for each user.";

SQL;

    return $sql;
}

function getDescription_24(): string
{
    return 'User display names with versioning';
}

