<?php

declare(strict_types = 1);

function getAllQueries_22(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `chat_message` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `text` VARCHAR(15000) NOT NULL COMMENT 'The message text',
  `user_id` varchar(36) NOT NULL,
  `room_id` varchar(36) NOT NULL,
  `reply_message_id` INT,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (user_id) REFERENCES user(id),
  FOREIGN KEY (room_id) REFERENCES room(id),
  CONSTRAINT uc_id UNIQUE (id)

) ENGINE=InnoDB DEFAULT 
    CHARSET=utf8mb4 
    COLLATE utf8mb4_0900_ai_ci 
    COMMENT="Table entries should be immutable."
    AUTO_INCREMENT = 100;
SQL;

    return $sql;
}


function getDescription_22(): string
{
    return 'Creating chat message table.';
}