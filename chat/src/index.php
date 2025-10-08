<?php

declare(strict_types=1);


use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Redis\RedisConfig;
use Amp\Socket;
use Amp\Websocket\Compression\Rfc7692CompressionFactory;
use Amp\Websocket\Server\Rfc6455Acceptor;
use Amp\Websocket\Server\Websocket;
use Bristolian\Config\Config;
use BristolianChat\ChatSpammer;
use Monolog\Logger;
use BristolianChat\ClientHandler;
use BristolianChat\FallbackHandler;
use BristolianChat\RedisWatcherRoomMessages;
use function Amp\ByteStream\getStdout;
use function Amp\Redis\createRedisClient;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/../../config.generated.php";
require __DIR__ . "/../../src/functions_chat.php";
require __DIR__ . "/../../src/functions_common.php";


// Logger
$logHandler = new StreamHandler(getStdout());
$logHandler->setFormatter(new ConsoleFormatter);
$logger = new Logger('server');
$logger->pushHandler($logHandler);

// Redis
$config = getGeneratedConfig();
$redis_config = $config[Config::BRISTOLIAN_REDIS_INFO];
$uri = sprintf(
    'redis://%s?password=%s',
    $redis_config['host'],
    $redis_config['password'],
);
$redis_config = RedisConfig::fromUri($uri);
$redis = createRedisClient($redis_config);


$clientHandler = new ClientHandler($logger);

$redisWatcher = new RedisWatcherRoomMessages(
    $redis,
    $clientHandler,
    $logger
);

$chat_spammer = new ChatSpammer($clientHandler, $logger);


// Websocket server
$server = SocketHttpServer::createForDirectAccess($logger);
$server->expose(new Socket\InternetAddress('0.0.0.0', 5000));
$acceptor = new Rfc6455Acceptor();

$websocket = new Websocket(
    httpServer: $server,
    logger: $logger,
    acceptor: $acceptor,
    clientHandler: $clientHandler,
    compressionFactory: new Rfc7692CompressionFactory(),
);

// Router
$errorHandler = new DefaultErrorHandler();
$router = new Router($server, $logger, $errorHandler);
$router->addRoute('GET', '/chat', $websocket);
// $router->setFallback(new DocumentRoot($server, $errorHandler, __DIR__ . '/../public'));
$router->setFallback(new FallbackHandler());

// Start all the things.
Amp\async($chat_spammer->run(...));
Amp\async($redisWatcher->run(...));
$server->start($router, $errorHandler);


// Run app until SIGINT or SIGTERM is received.
print "Await SIGINT or SIGTERM to be received." . PHP_EOL;
$signal = Amp\trapSignal([\SIGINT, \SIGTERM]);


// Shutting down
$logger->info(sprintf("Received signal %d, stopping HTTP server", $signal));
$server->stop();
$logger->info("fin");
