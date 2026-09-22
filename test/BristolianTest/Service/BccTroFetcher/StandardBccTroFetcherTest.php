<?php

namespace BristolianTest\Service\BccTroFetcher;

use Bristolian\Service\BccTroFetcher\StandardBccTroFetcher;
use Bristolian\Service\HttpFetcher\FakeHttpFetcherReturning404;
use Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use function Safe\file_get_contents;

#[CoversMethod(\Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::class, '__construct')]
#[CoversMethod(\Bristolian\Service\BccTroFetcher\StandardBccTroFetcher::class, 'fetchPage')]

class StandardBccTroFetcherTest extends BaseTestCase
{
    public function testFetchPageThrowsWhenHttpReturns404(): void
    {
        $httpFetcher = new FakeHttpFetcherReturning404();
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch content from');
        $this->expectExceptionMessage('HTTP 404');

        $fetcher->fetchPage();
    }

    public function testFetchPageReturnsHtmlWhenHttpReturns200(): void
    {
        $htmlContent = file_get_contents(__DIR__ . '/example_1.html');

        $httpFetcher = new FakeHttpFetcherWithFixedResponse(200, $htmlContent);
        $fetcher = new StandardBccTroFetcher($httpFetcher);

        $this->assertSame($htmlContent, $fetcher->fetchPage());
    }
}
