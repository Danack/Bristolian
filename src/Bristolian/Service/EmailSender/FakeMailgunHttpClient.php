<?php

declare(strict_types=1);

namespace Bristolian\Service\EmailSender;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 HTTP client that returns configurable responses for testing.
 * Used to drive Mailgun SDK behaviour without real network calls.
 */
final class FakeMailgunHttpClient implements ClientInterface
{
    private int $nextStatusCode = 200;

    private string $nextBody = '{"id":"test-id","message":"Queued"}';

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = new Response();
        $response = $response->withStatus($this->nextStatusCode);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withBody(new Stream('php://temp', 'rw'));
        $response->getBody()->write($this->nextBody);
        $response->getBody()->rewind();

        return $response;
    }

    public function setNextResponseStatusCode(int $statusCode): void
    {
        $this->nextStatusCode = $statusCode;
    }

    public function setNextBody(string $body): void
    {
        $this->nextBody = $body;
    }
}
