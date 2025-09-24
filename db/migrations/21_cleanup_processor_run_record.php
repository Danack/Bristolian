<?php

declare(strict_types = 1);

function getAllQueries_21(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `processor_run_record`
    CHANGE COLUMN `created_at` `start_time` datetime NOT NULL,
    ADD COLUMN `status` varchar(36) NOT NULL,
    ADD COLUMN `end_time` datetime;
SQL;

    return $sql;
}

function getDescription_21(): string
{
    return 'Refactor table names to be clearer.';
}