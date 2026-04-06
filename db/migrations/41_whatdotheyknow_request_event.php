<?php

declare(strict_types = 1);

function getDescription_41(): string
{
    return 'WhatDoTheyKnow request events (feed rows)';
}

function getAllQueries_41(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `whatdotheyknow_request_event` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `wdt_event_id` INT NOT NULL,
  `wdt_event_payload` JSON NOT NULL,
  `wdt_info_request_id` INT NOT NULL,
  `wdt_info_request_url_title` VARCHAR(512) NOT NULL,
  `wdt_user_id` INT NOT NULL,
  `wdt_user_url_name` VARCHAR(256) NOT NULL,
  `wdt_user_display_name` VARCHAR(512) NOT NULL,
  `wdt_public_body_id` INT NOT NULL,
  `wdt_event_occurred_at` DATETIME NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `whatdotheyknow_request_event_wdt_event_id` (`wdt_event_id`),
  KEY `whatdotheyknow_request_event_wdt_user_id` (`wdt_user_id`),
  KEY `whatdotheyknow_request_event_wdt_public_body_id` (`wdt_public_body_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT="Immutable rows from WhatDoTheyKnow JSON feed; wdt_* columns mirror API fields.";
SQL;

    return $sql;
}
