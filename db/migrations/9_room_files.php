<?php

declare(strict_types = 1);


function getAllQueries_9(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `room_file` (
  `room_id` varchar(36) NOT NULL,
  `stored_file_id` varchar(36) NOT NULL ,
  `description` varchar(1024) DEFAULT NULL COMMENT 'A short string to display in a list rather than the raw file name',
  `note` varchar(12000) DEFAULT NULL COMMENT 'A medium sized piece of text to explain what the file is.',
  `src_url` varchar(1024) DEFAULT NULL,
  `document_timestamp` DATETIME DEFAULT NULL COMMENT 'The datetime that the file was created or made public.',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (room_id) REFERENCES room(id),
  FOREIGN KEY (stored_file_id) REFERENCES stored_file(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    return $sql;
}

function getDescription_9(): string
{
    return 'Room files';
}