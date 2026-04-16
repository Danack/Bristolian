<?php

declare(strict_types=1);

namespace BristolianTest\Service\WhatDoTheyKnowFeedFetcher;

use Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
final class FakeWhatDoTheyKnowFeedFetcherReturningJsonTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson::__construct
     * @covers \Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson::fetchRequestedFromBristolCityCouncilJson
     */
    public function test_fetchRequestedFromBristolCityCouncilJson_returns_configured_json_body(): void
    {
        $jsonBody = '{"ok":true,"items":[1,2,3]}';
        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson($jsonBody);

        self::assertSame($jsonBody, $fetcher->fetchRequestedFromBristolCityCouncilJson());
    }
}
