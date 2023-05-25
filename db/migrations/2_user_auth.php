<?php

declare(strict_types = 1);


function getAllQueries_2(): array
{
    $sql = [];

    $sql[] = <<< SQL

CREATE TABLE `user_auth_email_password` (
  `id` varchar(36) NOT NULL,
  
  `user_id` varchar(36) NOT NULL,
  `email_address` varchar(2048) NOT NULL,
  `password_hash` varchar(2048) NOT NULL,

  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (user_id) REFERENCES user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";


SQL;

    return $sql;
}