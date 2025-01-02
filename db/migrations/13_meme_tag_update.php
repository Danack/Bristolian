<?php

declare(strict_types = 1);


function getAllQueries_13(): array
{
    $sql = [];

    $sql[] = <<< SQL
alter TABLE `meme_tag` 
ADD FOREIGN KEY fk_meme_id (meme_id) REFERENCES stored_meme(id);
SQL;
    return $sql;
}

function getDescription_13(): string
{
    return 'Add constraint for meme tags';
}