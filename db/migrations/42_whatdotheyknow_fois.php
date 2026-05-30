<?php

declare(strict_types = 1);

function getDescription_42(): string
{
    return 'WhatDoTheyKnow FOI requests and room associations';
}

function getAllQueries_42(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `whatdotheyknow_foi_request` (
  `id` varchar(36) NOT NULL,
  `wdt_info_request_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_whatdotheyknow_foi_request_id UNIQUE (id),
  CONSTRAINT uc_wdt_info_request_id UNIQUE (wdt_info_request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Canonical WhatDoTheyKnow request reference';
SQL;

    $sql[] = <<< SQL
CREATE TABLE `whatdotheyknow_room_foi_request` (
  `id` varchar(36) NOT NULL,
  `room_id` varchar(36) NOT NULL,
  `whatdotheyknow_foi_request_id` varchar(36) NOT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `description` varchar(12000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_whatdotheyknow_room_foi_request_id UNIQUE (id),
  FOREIGN KEY (room_id) REFERENCES room(id),
  FOREIGN KEY (whatdotheyknow_foi_request_id) REFERENCES whatdotheyknow_foi_request(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
SQL;

    return $sql;
}
