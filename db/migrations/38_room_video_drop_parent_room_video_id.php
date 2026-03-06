<?php

declare(strict_types=1);

function getDescription_38(): string
{
    return 'Drop parent_room_video_id column from room_video (no longer storing clip parent reference)';
}

function getAllQueries_38(): array
{
    $sql = [];

    // Drop FK first (MySQL names it room_video_ibfk_3 for the third FK on the table)
    $sql[] = "ALTER TABLE `room_video` DROP FOREIGN KEY `room_video_ibfk_3`";
    $sql[] = "ALTER TABLE `room_video` DROP COLUMN `parent_room_video_id`";

    return $sql;
}
