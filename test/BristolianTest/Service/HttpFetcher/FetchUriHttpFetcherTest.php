<?php

namespace BristolianTest\Service\HttpFetcher;

use Bristolian\Service\HttpFetcher\FetchUriHttpFetcher;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FetchUriHttpFetcherTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\HttpFetcher\FetchUriHttpFetcher::fetch
     */
    public function testFetchReturnsThreeElementArray(): void
    {
        $fetcher = new FetchUriHttpFetcher();
        // Use a URL that will yield a response (e.g. 200 or 404) without side effects
        $result = $fetcher->fetch('https://www.bristol.gov.uk/', 'GET');

        $this->assertCount(3, $result);
        // $result is [statusCode, body, headers] per HttpFetcher::fetch return type
    }
}
