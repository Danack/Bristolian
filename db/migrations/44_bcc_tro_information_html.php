<?php

declare(strict_types = 1);

function getDescription_44(): string
{
    return 'Store BCC TRO page HTML as text instead of JSON';
}

function getAllQueries_44(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `bcc_tro_information`
  MODIFY COLUMN `tro_data` MEDIUMTEXT NOT NULL
SQL;

    return $sql;
}
