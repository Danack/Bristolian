<?php

use Amp\Mysql\MysqlConnection;

require __DIR__ . '/chat_includes.php';

$injector = new \DI\Injector();
chatInjectionParams()->addToInjector($injector);
$injector->share($injector);

$mysql_client = $injector->make(MysqlConnection::class);

$result = $mysql_client->execute('select * from api_token');

while (($row = $result->fetchRow()) !== null) {
    var_dump($row);
}


echo "fin.";
