<?php

declare(strict_types=1);

namespace BristolianTest\Service\WhatDoTheyKnowFeedFetcher;

use Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson::class, '__construct')]
#[CoversMethod(\Bristolian\Service\WhatDoTheyKnowFeedFetcher\FakeWhatDoTheyKnowFeedFetcherReturningJson::class, 'fetchRequestedFromBristolCityCouncilJson')]

final class FakeWhatDoTheyKnowFeedFetcherReturningJsonTest extends BaseTestCase
{
    public function test_fetchRequestedFromBristolCityCouncilJson_returns_configured_json_body(): void
    {
        $jsonBody = '{"ok":true,"items":[1,2,3]}';
        $fetcher = new FakeWhatDoTheyKnowFeedFetcherReturningJson($jsonBody);

        self::assertSame($jsonBody, $fetcher->fetchRequestedFromBristolCityCouncilJson());
    }
}
