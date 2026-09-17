<?php

declare(strict_types=1);

namespace BristolianTest\Service\WhatDoTheyKnowFeedFetcher;

use Bristolian\Service\HttpFetcher\FakeHttpFetcherWithFixedResponse;
use Bristolian\Service\WhatDoTheyKnowFeedFetcher\StandardWhatDoTheyKnowFeedFetcher;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\WhatDoTheyKnowFeedFetcher\StandardWhatDoTheyKnowFeedFetcher::class, '__construct')]
#[CoversMethod(\Bristolian\Service\WhatDoTheyKnowFeedFetcher\StandardWhatDoTheyKnowFeedFetcher::class, 'fetchRequestedFromBristolCityCouncilJson')]

final class StandardWhatDoTheyKnowFeedFetcherTest extends BaseTestCase
{
    public function test_fetch_returns_body_on_200(): void
    {
        $httpFetcher = new FakeHttpFetcherWithFixedResponse(200, '{"ok":true}');
        $fetcher = new StandardWhatDoTheyKnowFeedFetcher($httpFetcher);

        self::assertSame('{"ok":true}', $fetcher->fetchRequestedFromBristolCityCouncilJson());
    }

    public function test_fetch_throws_on_non_200(): void
    {
        $httpFetcher = new FakeHttpFetcherWithFixedResponse(503, 'unavailable');
        $fetcher = new StandardWhatDoTheyKnowFeedFetcher($httpFetcher);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch WhatDoTheyKnow feed (HTTP 503)');

        $fetcher->fetchRequestedFromBristolCityCouncilJson();
    }
}
