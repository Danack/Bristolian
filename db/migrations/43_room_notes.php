<?php

declare(strict_types = 1);

function getDescription_43(): string
{
    return 'Room notes (markdown documents) and note tags';
}

function getAllQueries_43(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `room_note` (
  `id` varchar(36) NOT NULL,
  `room_id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL COMMENT 'User who created the note',
  `title` varchar(1024) NOT NULL,
  `markdown` MEDIUMTEXT NOT NULL COMMENT 'Current markdown body; not versioned',
  `document_timestamp` DATETIME DEFAULT NULL COMMENT 'The datetime that the note was created or made public.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_room_note_id UNIQUE (id),
  FOREIGN KEY (room_id) REFERENCES room(id),
  FOREIGN KEY (user_id) REFERENCES user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT="Room markdown notes; body is mutable and unversioned.";
SQL;

    $sql[] = <<< SQL
CREATE TABLE `room_note_tag` (
  `room_note_id` varchar(36) NOT NULL,
  `tag_id` varchar(36) NOT NULL,
  PRIMARY KEY (`room_note_id`, `tag_id`),
  CONSTRAINT `room_note_tag_room_note_fk` FOREIGN KEY (`room_note_id`) REFERENCES `room_note` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_note_tag_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `room_tag` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

    return $sql;
}
