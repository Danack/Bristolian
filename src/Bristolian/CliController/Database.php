<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Config\Config;
use Bristolian\Model\Types\MigrationFromCode;
use Bristolian\Model\Types\MigrationThatHasBeenRun;
use Bristolian\PdoSimple\PdoSimple;

/**
 * @codeCoverageIgnore
 * @return int
 */
function require_all_migration_files(): int
{
    $glob_pattern = __DIR__ . "/../../../db/migrations/*.php";

    $files = glob($glob_pattern);

    $max_migration_number = 0;
    $numbers = [];

    foreach ($files as $file) {
        echo $file . "\n";
        require_once $file;
        $filename = basename($file);

        $underscore_position = strpos($filename, '_');

        if ($underscore_position === false) {
            echo "File $file does not contain an underscore, which is required by the naming convention.";
            exit(-1);
        }

        $number = (int)substr($filename, 0, $underscore_position);
        if ($number === 0) {
            echo "File $file appears not to have a valid int at the start.";
            exit(-1);
        }

        // TODO - check numbers are continuous/contiguous
        // $numbers[] = $number;
        if ($number > $max_migration_number) {
            $max_migration_number = $number;
        }

        require_once $file;
    }

    return $max_migration_number;
}


/**
 * @codeCoverageIgnore
 * @param PdoSimple $pdo
 * @return void
 */
function ensureMigrationsTableExists(PdoSimple $pdo): void
{
    $sql = <<< SQL
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int AUTO_INCREMENT NOT NULL,
  `description` varchar(1024) NOT NULL,
  `json_encoded_queries` MEDIUMTEXT NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    $pdo->execute($sql, []);
}

/**
 * @codeCoverageIgnore
 * @param mixed[] $queries
 * @return string
 * @throws \Exception
 */
function getQueriesSha(array $queries): string
{
    return hash('SHA256', json_encode_safe($queries));
}

/**
 * @codeCoverageIgnore
 * @param PdoSimple $pdo
 * @param MigrationFromCode[] $list_of_migrations_that_need_to_be_run
 * @return void
 */
function runAllQueries(PdoSimple $pdo, array $list_of_migrations_that_need_to_be_run): void
{
    foreach ($list_of_migrations_that_need_to_be_run as $i => $migrationFromCode) {
        $json_encoded_queries = json_encode_safe($migrationFromCode->queries_to_run);

//        printf(
//            "Query %d description %s queries: %s\n",
//            $i,
//            $migrationFromCode->description,
//            $json_encoded_queries
//        );

        $migration_sql_index = 0;

        foreach ($migrationFromCode->queries_to_run as $query) {
            printf(
                "Running migration %d part %d\n",
                $i,
                $migration_sql_index
            );
            $pdo->execute($query, []);
            $migration_sql_index += 1;
        }

        $sql = <<< SQL
insert into migrations (
    id, 
    description,
    json_encoded_queries
)
values (
  :id,
  :description,
  :json_encoded_queries
)
SQL;

        $params = [
            ':id' => $i,
            ':description' => $migrationFromCode->description,
            ':json_encoded_queries' => $json_encoded_queries
        ];

        $statement = $pdo->get_pdo()->prepare($sql);
        $statement->execute($params);
    }
}

/**
 * @codeCoverageIgnore
 * @param int $max_migration_number
 * @return MigrationFromCode[]
 * @throws \DataType\Exception\ValidationException
 */
function findWhichMigrationsNeedToBeRun(
    PdoSimple $pdoSimple,
    int $max_migration_number,
): array {
    /** @var MigrationFromCode[] $migrations_from_code */
    $migrations_from_code = [];

    for ($i = 1; $i <= $max_migration_number; $i += 1) {
        $function_name_sql = 'getAllQueries_' . $i;
        $function_name_description = 'getDescription_' . $i;

        if (function_exists($function_name_sql) !== true) {
            echo "DB migration function [$function_name_sql] does not exist.";
            exit(-1);
        }
        if (function_exists($function_name_description) !== true) {
            echo "DB migration function [$function_name_description] does not exist.";
            exit(-1);
        }

        $migrations_from_code[$i] = new MigrationFromCode(
            $i,
            $function_name_description(),
            $function_name_sql()
        );
    }

    $sql = "select * from migrations order by id ASC";
    $migrations_from_db = $pdoSimple->fetchAllAsObjectConstructor($sql, [], MigrationThatHasBeenRun::class);

    $migrations_from_db_indexed = [];

    foreach ($migrations_from_db as $migration_from_db) {
        $migrations_from_db_indexed[$migration_from_db->id] = $migration_from_db;
    }

    $migrations_to_run = [];
    $migrations_from_db = null;

    foreach ($migrations_from_code as $i => $migration_from_code) {
        // migration hasn't been run, we have to run it.
        if (array_key_exists($i, $migrations_from_db_indexed) === false) {
            $migrations_to_run[$i] = $migration_from_code;
            echo "Need to run migration $i\n";
            continue;
        }

        $migration_from_db = $migrations_from_db_indexed[$i];
        $json_encoded_queries_for_migration = json_encode_safe($migration_from_code->queries_to_run);

        if ($migration_from_db->json_encoded_queries != $json_encoded_queries_for_migration) {
            echo "Migration $i defined in code does not match migration as run on server\n";
            echo "Code: $json_encoded_queries_for_migration\n";
            echo "DB  : " . $migration_from_db->json_encoded_queries . "\n";

            exit(-1);
        }

        echo "No need to run migration $i \n";
    }

    return $migrations_to_run;
}

/**
 * Code that helps manage the DB from the command line.
 * Not currently unit-tested as currently not worth it.
 *
 * @codeCoverageIgnore
 */
class Database
{
    public function waitForDBToBeWorking(
        Config $config,
        int $maxTimeToWait = null
    ): void {
        if ($maxTimeToWait === null) {
            $maxTimeToWait = 60;
        }

        $startTime = microtime(true);

        do {
            echo "Attempting to connect to DB.\n";
            try {
                $pdo = createPDOForUser($config);
                $pdo->query('SELECT 1');
                echo "DB appears to be available.\n";
                return;
            } catch (\Exception $e) {
                echo "DB not available yet. \n";
                echo $e->getMessage();
                echo "\n";
            }

            sleep(1);
        } while ((microtime(true) - $startTime) < $maxTimeToWait);
    }

    /**
     * @param PdoSimple $pdoSimple
     * @return void
     */
    public function performMigrations(PdoSimple $pdoSimple)
    {
        ensureMigrationsTableExists($pdoSimple);

        $max_migration_number = require_all_migration_files();

        echo "max_migration_number = $max_migration_number \n";
        $list_of_migrations_that_need_to_be_run = findWhichMigrationsNeedToBeRun(
            $pdoSimple,
            $max_migration_number
        );

        runAllQueries($pdoSimple, $list_of_migrations_that_need_to_be_run);
    }
}
