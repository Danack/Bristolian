<?php

declare(strict_types = 1);


function getAllQueries_32(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `api_token` (
  `id` varchar(36) NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'The API token value',
  `name` varchar(256) NOT NULL COMMENT 'Name/identifier for the token (e.g., "John\'s iPhone", "Test Device")',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_revoked` boolean NOT NULL DEFAULT false COMMENT 'For soft deletion',
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT uc_api_token_id UNIQUE (id),
  CONSTRAINT uc_api_token_token UNIQUE (token),
  INDEX idx_api_token_token (token),
  INDEX idx_api_token_is_revoked (is_revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="API tokens for mobile app authentication.";
SQL;

    return $sql;
}

function getDescription_32(): string
{
    return 'API token table for mobile app authentication';
}
