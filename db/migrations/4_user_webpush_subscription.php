<?php

declare(strict_types = 1);


function getDescription_4(): string
{
    return 'Web Push notifications.';
}

function getAllQueries_4(): array
{
    $sql = [];

    $sql[] = <<< SQL


CREATE TABLE `user_webpush_subscription` (
  
  `user_webpush_subscription_id` BIGINT AUTO_INCREMENT NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `endpoint` varchar(1024) NOT NULL,
  `expiration_time` varchar(512) NOT NULL,
  `raw` varchar(2048) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_webpush_subscription_id`),
  FOREIGN KEY (user_id) REFERENCES user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Raw may be trimmed to fit";
SQL;

    return $sql;
}