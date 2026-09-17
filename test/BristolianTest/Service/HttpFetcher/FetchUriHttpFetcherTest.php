<?php

namespace BristolianTest\Service\HttpFetcher;

use Bristolian\Service\HttpFetcher\FetchUriHttpFetcher;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\HttpFetcher\FetchUriHttpFetcher::class, 'fetch')]

class FetchUriHttpFetcherTest extends BaseTestCase
{
    /**
     * @group slow
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
