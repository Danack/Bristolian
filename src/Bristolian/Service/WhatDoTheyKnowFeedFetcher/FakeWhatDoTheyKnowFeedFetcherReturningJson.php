<?php

declare(strict_types=1);

namespace Bristolian\Service\WhatDoTheyKnowFeedFetcher;

/**
 * Test double that always returns a fixed JSON body from fetchRequestedFromBristolCityCouncilJson().
 */
final class FakeWhatDoTheyKnowFeedFetcherReturningJson implements WhatDoTheyKnowFeedFetcher
{
    public function __construct(
        private readonly string $jsonBody
    ) {
    }

    public function fetchRequestedFromBristolCityCouncilJson(): string
    {
        return $this->jsonBody;
    }
}
