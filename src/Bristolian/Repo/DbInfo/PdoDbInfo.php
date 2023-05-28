<?php

namespace Bristolian\Repo\DbInfo;

use PDO;
use Bristolian\DataType\Table;

class PdoDbInfo implements DbInfo
{
    public function __construct(private PDO $pdo)
    {
    }

    function getTableInfo(): array
    {
        $sql = <<< SQL
SELECT table_name, table_rows
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = 'bristolian';
SQL;

        $statement = $this->pdo->query($sql);
        $tables_in_bristolian = $statement->fetchAll();

        return Table::createArrayOfTypeFromArray($tables_in_bristolian);
    }
}
