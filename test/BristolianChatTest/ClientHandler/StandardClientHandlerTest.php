<?php

declare(strict_types=1);

namespace BristolianChatTest\ClientHandler;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use BristolianChat\ClientHandler\StandardClientHandler;
use BristolianChatTest\Fixtures\FakeHttpClient;
use BristolianChatTest\Fixtures\FakeWebsocketClient;
use BristolianChatTest\Fixtures\Psr7UriForTests;
use BristolianTest\BaseTestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

/**
 * @coversNothing
 */
class StandardClientHandlerTest extends BaseTestCase
{
    /**
     * @covers \BristolianChat\ClientHandler\StandardClientHandler::__construct
     * @covers \BristolianChat\ClientHandler\StandardClientHandler::broadcastText
     */
    public function test_broadcastText_can_be_called_without_connected_clients(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $clientHandler = new StandardClientHandler($logger);

        $clientHandler->broadcastText('test payload', []);

        $this->addToAssertionCount(1);
    }

    /**
     * @covers \BristolianChat\ClientHandler\StandardClientHandler::handleClient
     */
    public function test_handleClient_adds_client_logs_and_processes_messages(): void
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($testHandler);

        $clientHandler = new StandardClientHandler($logger);

        $remoteAddress = '127.0.0.1:54321';
        $client = new FakeWebsocketClient(42, $remoteAddress, ['hello', 'world']);
        $httpClient = new FakeHttpClient(1, '127.0.0.1:9999', '0.0.0.0:80');
        $uri = Psr7UriForTests::fromString('http://localhost/');
        $request = new Request($httpClient, 'GET', $uri, [], '', '1.1', null);
        $response = new Response(200);

        $clientHandler->handleClient($client, $request, $response);

        $records = $testHandler->getRecords();
        $this->assertCount(1, $records);
        $this->assertSame("Added client: {$remoteAddress}", $records[0]['message']);
    }
}
