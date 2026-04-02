<?php

declare(strict_types = 1);

function getDescription_40(): string
{
    return 'User ownership table';
}

function getAllQueries_40(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `user_ownership` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `type` varchar(256) NOT NULL,
  `room_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `user_ownership_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `user_ownership_room_fk` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT="Records of user ownership by type, optionally scoped to a room.";
SQL;

    return $sql;
}
