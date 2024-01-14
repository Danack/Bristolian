<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\DataType\Migration;
use PDO;
use Bristolian\DataType\Table;

class PdoDbInfo implements DbInfo
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array|Table[]
     * @throws \DataType\Exception\ValidationException
     */
    public function getTableInfo(): array
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

    /**
     * @return Migration[]
     */
    public function getMigrations(): array
    {
        $sql = <<< SQL
SELECT id, description, checksum, created_at
    FROM migrations
    order by created_at
SQL;

        $statement = $this->pdo->query($sql);
        $migration_data = $statement->fetchAll();

        return Migration::createArrayOfTypeFromArray($migration_data);
    }
}
