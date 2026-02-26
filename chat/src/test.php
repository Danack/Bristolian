<?php

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Mysql\MysqlConfig;
use Amp\Redis\RedisConfig;
use Amp\Socket;
use Amp\Websocket\Compression\Rfc7692CompressionFactory;
use Amp\Websocket\Server\Rfc6455Acceptor;
use Amp\Websocket\Server\Websocket;

use Bristolian\Config\Config;
use BristolianChat\ChatSpammer;
use Monolog\Logger;
use BristolianChat\ClientHandler\StandardClientHandler;
use BristolianChat\FallbackHandler;
use BristolianChat\RoomMessagesWatcher\SqlRoomMessagesWatcher;

use function Amp\ByteStream\getStdout;
use function Amp\Redis\createRedisClient;
use function Amp\Mysql\connect as mysql_connect;

require __DIR__ . '/chat_includes.php';


$config = getGeneratedConfig();

// MySql
$mysql_config = new MysqlConfig(
    $config[Config::BRISTOLIAN_SQL_HOST],
    MysqlConfig::DEFAULT_PORT,
    $config[Config::BRISTOLIAN_SQL_USERNAME],
    $config[Config::BRISTOLIAN_SQL_PASSWORD],
    $config[Config::BRISTOLIAN_SQL_DATABASE],
);
$mysql_client = mysql_connect($mysql_config);


$result = $mysql_client->execute('select * from api_token');

while (($row = $result->fetchRow()) !== null) {
    var_dump($row);
}


echo "fin.";
