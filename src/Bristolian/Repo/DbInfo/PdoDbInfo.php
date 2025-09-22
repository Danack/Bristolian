<?php

namespace Bristolian\Repo\DbInfo;

use Bristolian\Parameters\MigrationThatHasBeenRun;
use Bristolian\Parameters\Table;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Database\migrations;
use PDO;

class PdoDbInfo implements DbInfo
{
    public function __construct(
        private PDO $pdo,
        private PdoSimple $pdoSimple
    ) {
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
     * @return MigrationThatHasBeenRun[]
     */
    public function getMigrations(): array
    {
        $sql = migrations::SELECT . " order by created_at";

//
//            <<< SQL
//SELECT id, description, json_encoded_queries, created_at
//    FROM migrations
//    order by created_at
//SQL;

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            [],
            MigrationThatHasBeenRun::class
        );
//        $migration_data = $statement->();
//        return MigrationThatHasBeenRun::createArrayOfTypeFromArray($migration_data);
    }
}
