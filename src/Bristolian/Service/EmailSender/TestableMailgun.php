<?php

declare(strict_types=1);

namespace Bristolian\Service\EmailSender;

use Mailgun\HttpClient\HttpClientConfigurator;
use Mailgun\Mailgun;
use Psr\Http\Client\ClientInterface;

/**
 * Mailgun subclass for tests. Extends Mailgun and provides a factory that
 * uses a custom HTTP client (e.g. FakeMailgunHttpClient) so behaviour can be
 * driven without real network calls.
 */
class TestableMailgun extends Mailgun
{
    public static function createWithHttpClient(ClientInterface $httpClient): self
    {
        $configurator = new HttpClientConfigurator();
        $configurator->setHttpClient($httpClient);
        $configurator->setApiKey('test-api-key');
        $configurator->setEndpoint('https://api.mailgun.net');

        return new self($configurator);
    }
}
