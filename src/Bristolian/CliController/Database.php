<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Config;
use PDO;


function require_all_migration_files()
{
    $glob_pattern = __DIR__ . "/../../../db/migrations/*.php";

    $files = glob($glob_pattern);

    $max_migration_number = 0;
    $numbers = [];

    foreach ($files as $file) {
        echo $file;
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


function ensureMigrationsTableExists(PDO $pdo)
{
    $sql = <<< SQL
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int AUTO_INCREMENT NOT NULL,
  `description` varchar(1024) NOT NULL,
  `checksum` varchar(1024) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT="Table entries should be immutable.";

SQL;

    $pdo->exec($sql);
}


function getQueriesSha($queries)
{
    return hash('SHA256', json_encode_safe($queries));
}

function runAllQueries(PDO $pdo, $list_of_migrations_that_need_to_be_run)
{
    foreach ($list_of_migrations_that_need_to_be_run as $i => $queries)
    {
        $sha = getQueriesSha($queries);
        printf(
            "Query %d sha: %s\n",
            $i,
            $sha
        );

        $statement = $pdo->prepare("insert into migrations (
                        description,
                        checksum
                    ) values (:description, :checksum)");

        $statement->execute([
            ':description' => "Migration $i",
            ':checksum' => $sha
        ]);
    }
}


function findWhichMigrationsNeedToBeRun(PDO $pdo, $max_migration_number)
{
    $db_query_list = [];

    for ($i = 1; $i <= $max_migration_number; $i += 1) {
        $function_name = 'getAllQueries_' . $i;

        if (function_exists($function_name) !== true) {
            echo "DB migration function [$function_name] does not exist.";
            exit(-1);
        }

        $db_query_list[$i] = $function_name();
    }

    $result = $pdo->query("select * from migrations order by id ASC");
    $migrations = $result->fetchAll();

    var_dump($migrations);

    return $db_query_list;
}

class Database
{
    public function waitForDBToBeWorking(
        Config $config,
        int $maxTimeToWait = null)
    {
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
                echo "DB not available yet.\n";
            }

            sleep(1);
        } while ((microtime(true) - $startTime) < $maxTimeToWait);
    }

    /**
     * @param PDO $pdo
     * @return void
     */
    public function performMigrations(PDO $pdo)
    {
        ensureMigrationsTableExists($pdo);

        $max_migration_number = require_all_migration_files();

        echo "max_migration_number = $max_migration_number \n";
        $list_of_migrations_that_need_to_be_run = findWhichMigrationsNeedToBeRun(
            $pdo,
            $max_migration_number
        );

        runAllQueries($pdo, $list_of_migrations_that_need_to_be_run);


    }
}



