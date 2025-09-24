<?php

declare(strict_types = 1);

function getAllQueries_20(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE stored_file
    RENAME TO room_file_object_info,
    COMMENT = 'Stores metadata about room file objects';

SQL;

    $sql[] = <<< SQL
ALTER TABLE stored_stair_image_file
    RENAME TO stair_image_object_info,
    COMMENT = 'Stores metadata about stair image objects';
SQL;

    return $sql;
}

function getDescription_20(): string
{
    return 'Refactor table names to be clearer.';
}