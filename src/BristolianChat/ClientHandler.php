<?php

namespace BristolianChat;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Websocket\Server\WebsocketClientGateway;
use Amp\Websocket\Server\WebsocketClientHandler;
use Amp\Websocket\Server\WebsocketGateway;
use Amp\Websocket\WebsocketClient;
use Monolog\Logger;

class ClientHandler implements WebsocketClientHandler
{
    public function __construct(
        private Logger $logger,
        private readonly WebsocketGateway $gateway = new WebsocketClientGateway(),
    ) {
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
            $text = sprintf('%d: %s', $client->getId(), (string)$message);
            $this->gateway->broadcastText($text)->ignore();
        }
    }
}
