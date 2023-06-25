<?php

declare(strict_types = 1);

function getDescription_4(): string
{
    return 'organisations';
}

function getAllQueries_4(): array
{
    $sql = [];

    $sql[] = <<< SQL
CREATE TABLE `organisations` (  
  `organisation_id` varchar(36) NOT NULL,
  `name` varchar(36) NOT NULL,
  `description` varchar(36) NOT NULL,
  `homepage_link` varchar(36) NOT NULL,
  `facebook_link` varchar(36) NOT NULL,
  `instagram_link` varchar(36) NOT NULL,
  `twitter_url` varchar(36) NOT NULL,
  `youtube_link` varchar(36) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`organisation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Description and text can be mutated.";

SQL;

    return $sql;
}