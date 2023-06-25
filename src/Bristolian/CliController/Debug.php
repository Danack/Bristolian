<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Config;
use PDO;
use Bristolian\DataType\Migration;
use function DataType\createArrayOfType;

class Debug
{
    public function basic(): void
    {
        echo "Debug commands seem to be working.\n";
    }

    /**
     * @param PDO $pdo
     * @return void
     */
    public function db(PDO $pdo)
    {
        $sql = <<< SQL
select * from user_auth_email_password
SQL;

        $pdo->query($sql);
    }
}
