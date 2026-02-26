<?php

use Bristolian\Config\Config;

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
use function Amp\ByteStream\getStdout;
use function Amp\Redis\createRedisClient;
use function Amp\Mysql\connect as mysql_connect;



require_once __DIR__ . "/../chat/src/chat_includes.php";



/**
 * Returns a unique per test run id. Though doesn't account for tests
 * running in parallel.
 * @return string
 */
function create_test_uniqid(): string
{
    static $counter = 0;
    static $previous_time = null;

    $new_time = time();

    if ($previous_time === null || $new_time > $previous_time) {
        // seconds have changed.
        $counter = 0;
    }

    $id = 'time_' . time() . '_counter_' . $counter . '_rand_' . random_int(1000, 9999);;

    $counter += 1;

    return $id;
}


/**
 * @return \DI\Injector
 */
function createTestInjector()
{
//    $injectionParams = testInjectionParams();

    $injector = new \DI\Injector();
//    $injectionParams->addToInjector($injector);

//    foreach ($shareDoubles as $shareDouble) {
//        $injector->share($shareDouble);
//    }

    $injector->share($injector); //Yolo ServiceLocator
    return $injector;
}

function createMysqlClient(): \Amp\Mysql\MysqlConnection
{

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

    return $mysql_client;
}