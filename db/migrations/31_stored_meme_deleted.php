<?php

declare(strict_types = 1);


function getAllQueries_31(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `stored_meme`
ADD COLUMN `deleted` BOOLEAN NOT NULL DEFAULT FALSE 
COMMENT 'Indicates if the meme file is no longer available in storage'
AFTER `created_at`;
SQL;

    return $sql;
}

function getDescription_31(): string
{
    return 'Add deleted column to stored_meme table';
}
