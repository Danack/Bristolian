<?php

declare(strict_types=1);

function getDescription_37(): string
{
    return 'Add unique key (room_video_id, language) to room_video_transcript';
}

function getAllQueries_37(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `room_video_transcript`
  ADD CONSTRAINT uc_room_video_transcript_room_video_language UNIQUE (`room_video_id`, `language`);
SQL;

    return $sql;
}
