<?php





function getAllQueries_20(): array
{
    $sql = [];

    $sql[] = <<< SQL
ALTER TABLE `bristol_stair_info`
  MODIFY COLUMN `latitude` DOUBLE DEFAULT NULL COMMENT 'Where they are. We allow null to simplify uploading.',
  MODIFY COLUMN `longitude` DOUBLE DEFAULT NULL COMMENT 'Where they are. We allow null to simplify uploading.';
SQL;

    return $sql;
}

function getDescription_20(): string
{
    return 'Stair positions need to be double.';
}