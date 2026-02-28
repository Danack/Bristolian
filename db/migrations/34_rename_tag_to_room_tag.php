<?php

declare(strict_types = 1);

function getDescription_34(): string
{
    return 'Rename tag to room_tag and add room_id with FK to room';
}

function getAllQueries_34(): array
{
    $sql = [];

    // Existing tag rows have no room_id; truncate so we can add NOT NULL room_id
    $sql[] = "TRUNCATE TABLE `tag`";

    $sql[] = "RENAME TABLE `tag` TO `room_tag`";

    $sql[] = "ALTER TABLE `room_tag` ADD COLUMN `room_id` varchar(36) NOT NULL AFTER `tag_id`";

    $sql[] = "ALTER TABLE `room_tag` ADD CONSTRAINT `room_tag_room_id_fk` FOREIGN KEY (`room_id`) REFERENCES `room`(`id`)";

    return $sql;
}
