<?php

declare(strict_types = 1);

function getAllQueries_33(): array
{
    $sql = [];

    // Drop FK so we can rename the column; leave the unique constraint (it will apply to the renamed column)
    $sql[] = "ALTER TABLE `room_sourcelink` DROP FOREIGN KEY `room_sourcelink_ibfk_2`";

    $sql[] = "RENAME TABLE `sourcelink` TO `annotation`, `room_sourcelink` TO `room_annotation`";

    $sql[] = "ALTER TABLE `room_annotation` CHANGE `sourcelink_id` `annotation_id` varchar(36) NOT NULL";

    $sql[] = "ALTER TABLE `room_annotation` ADD CONSTRAINT `room_annotation_annotation_id_fk` FOREIGN KEY (`annotation_id`) REFERENCES `annotation`(`id`)";

    return $sql;
}

function getDescription_33(): string
{
    return 'Rename sourcelink/room_sourcelink to annotation/room_annotation';
}
