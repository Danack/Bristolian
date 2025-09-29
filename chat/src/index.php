<?php

declare(strict_types=1);

use Amp\Http\HttpStatus;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\SocketHttpServer;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket;
use Amp\Websocket\Compression\Rfc7692CompressionFactory;
use Amp\Websocket\Server\AllowOriginAcceptor;
use Amp\Websocket\Server\Websocket;
use Amp\Websocket\Server\WebsocketClientGateway;
use Amp\Websocket\Server\WebsocketClientHandler;
use Amp\Websocket\Server\WebsocketGateway;
use Amp\Websocket\WebsocketClient;
use Monolog\Logger;
use Amp\Websocket\Server\Rfc6455Acceptor;
use Amp\Redis\RedisClient;
use Amp\Redis\RedisConfig;
use Bristolian\Keys\ContentModifiedKey;
use Bristolian\Config\Config;

use function Amp\ByteStream\getStdout;
use function Amp\Redis\createRedisClient;


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . "/../../config.generated.php";


class FallbackHandler implements RequestHandler
{
    public function handleRequest(Request $request): Response
    {
        // TODO: Implement handleRequest() method.

        $response = new Response(HttpStatus::OK,
            [],
            "This is a default response. Maybe try a specific end-point..."
        );

        return $response;
    }
}

$logHandler = new StreamHandler(getStdout());
$logHandler->setFormatter(new ConsoleFormatter);

$logger = new Logger('server');
$logger->pushHandler($logHandler);

$config = getGeneratedConfig();

$redis_config = $config[Config::BRISTOLIAN_REDIS_INFO];

$uri = sprintf(
    'redis://%s?password=%s',
    $redis_config['host'],
    $redis_config['password'],
);

$redis_config = RedisConfig::fromUri($uri);
$redis = createRedisClient($redis_config);


$server = SocketHttpServer::createForDirectAccess($logger);
$server->expose(new Socket\InternetAddress('0.0.0.0', 5000));
$acceptor = new Rfc6455Acceptor();

$clientHandler = new class($logger) implements WebsocketClientHandler {
    public function __construct(
        private Logger $logger,
        private readonly WebsocketGateway $gateway = new WebsocketClientGateway(),
    ){
    }

    public function getGateway(): WebsocketClientGateway
    {
        return $this->gateway;
    }

    public function handleClient(WebsocketClient $client, Request $request, Response $response): void
    {
        $this->gateway->addClient($client);
        $this->logger->info("Added client: " . $client->getRemoteAddress()->toString());

        while ($message = $client->receive()) {
            $this->gateway->broadcastText(sprintf('%d: %s', $client->getId(), (string)$message))->ignore();
        }
    }
};



// Run a loop that waits for an event to appear at a specific key
$redis_loop = function () use ($redis, $clientHandler, $logger) {
    $key = '/events';

    while (true) {
        try {
            $list = $redis->getList(ContentModifiedKey::getAbsoluteKeyName());
            $item = $list->popHead();

            if ($item !== null) {
                $logger->info("Received event from Redis: " . $item);
                $clientHandler->getGateway()->broadcastText($item)->ignore();
            }
        } catch (\Throwable $e) {
            $logger->error("Redis loop error: " . $e->getMessage());
        }

        \Amp\delay(0.5); // Wait a bit before retrying
    }
};

Amp\async($redis_loop);

$websocket = new Websocket(
    httpServer: $server,
    logger: $logger,
    acceptor: $acceptor,
    clientHandler: $clientHandler,
    compressionFactory: new Rfc7692CompressionFactory(),
);

$errorHandler = new DefaultErrorHandler();

$router = new Router($server, $logger, $errorHandler);
$router->addRoute('GET', '/chat', $websocket);
// $router->setFallback(new DocumentRoot($server, $errorHandler, __DIR__ . '/../public'));
$router->setFallback(new FallbackHandler());
$server->start($router, $errorHandler);

// Await SIGINT or SIGTERM to be received.
print "Await SIGINT or SIGTERM to be received." . PHP_EOL;
$signal = Amp\trapSignal([\SIGINT, \SIGTERM]);


$logger->info(sprintf("Received signal %d, stopping HTTP server", $signal));

$server->stop();

print "fin\n";