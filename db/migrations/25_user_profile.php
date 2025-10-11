<?php

declare(strict_types = 1);


function getAllQueries_25(): array
{
    $sql = [];

    $sql[] = <<< SQL

CREATE TABLE `user_profile` (
  `user_id` varchar(36) NOT NULL COMMENT 'The user this profile belongs to',
  `avatar_image_id` varchar(36) DEFAULT NULL COMMENT 'UUID7 for the profile picture/avatar',
  `about_me` varchar(4096) DEFAULT NULL COMMENT 'About me text, up to 4096 characters',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP 
                     ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (user_id) REFERENCES user(id)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="User profile information - not versioned, just current values.";

SQL;

    return $sql;
}

function getDescription_25(): string
{
    return 'User profile with avatar and about me';
}

