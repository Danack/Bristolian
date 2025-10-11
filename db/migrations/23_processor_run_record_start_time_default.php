<?php

declare(strict_types = 1);

function getAllQueries_23(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `processor_run_record`
    MODIFY COLUMN `start_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP;
SQL;

    return $sql;
}

function getDescription_23(): string
{
    return 'Add DEFAULT CURRENT_TIMESTAMP to processor_run_record.start_time column';
}

