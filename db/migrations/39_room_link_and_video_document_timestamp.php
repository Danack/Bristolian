<?php

declare(strict_types = 1);

function getDescription_39(): string
{
    return 'Add document_timestamp to room_link and room_video';
}

function getAllQueries_39(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `room_link`
  ADD COLUMN `document_timestamp` DATETIME DEFAULT NULL COMMENT 'The datetime that the linked document was created or made public.'
SQL;

    $sql[] = <<< SQL
ALTER TABLE `room_video`
  ADD COLUMN `document_timestamp` DATETIME DEFAULT NULL COMMENT 'The datetime that the underlying video was created or made public (e.g. YouTube publish date).'
SQL;

    return $sql;
}

