<?php

declare(strict_types = 1);

function getDescription_35(): string
{
    return 'Junction tables for room tags on files, links, and annotations';
}

function getAllQueries_35(): array
{
    $sql = [];

    // room_file has no PRIMARY or UNIQUE; add UNIQUE so we can reference it from room_file_tag
    $sql[] = "ALTER TABLE `room_file` ADD UNIQUE KEY `uc_room_id_stored_file_id` (`room_id`, `stored_file_id`)";

    $sql[] = <<< SQL
CREATE TABLE `room_file_tag` (
  `room_id` varchar(36) NOT NULL,
  `stored_file_id` varchar(36) NOT NULL,
  `tag_id` varchar(36) NOT NULL,
  PRIMARY KEY (`room_id`, `stored_file_id`, `tag_id`),
  CONSTRAINT `room_file_tag_room_file_fk` FOREIGN KEY (`room_id`, `stored_file_id`) REFERENCES `room_file` (`room_id`, `stored_file_id`) ON DELETE CASCADE,
  CONSTRAINT `room_file_tag_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `room_tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
SQL;

    $sql[] = <<< SQL
CREATE TABLE `room_link_tag` (
  `room_link_id` varchar(36) NOT NULL,
  `tag_id` varchar(36) NOT NULL,
  PRIMARY KEY (`room_link_id`, `tag_id`),
  CONSTRAINT `room_link_tag_room_link_fk` FOREIGN KEY (`room_link_id`) REFERENCES `room_link` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_link_tag_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `room_tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
SQL;

    $sql[] = <<< SQL
CREATE TABLE `room_annotation_tag` (
  `room_annotation_id` varchar(36) NOT NULL,
  `tag_id` varchar(36) NOT NULL,
  PRIMARY KEY (`room_annotation_id`, `tag_id`),
  CONSTRAINT `room_annotation_tag_room_annotation_fk` FOREIGN KEY (`room_annotation_id`) REFERENCES `room_annotation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_annotation_tag_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `room_tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
SQL;

    return $sql;
}
