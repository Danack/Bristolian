<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\Service\WhatDoTheyKnowFeedFetcher\WhatDoTheyKnowFeedFetcher;

final class WhatDoTheyKnowFeedFetcherThatThrows implements WhatDoTheyKnowFeedFetcher
{
    public function __construct(
        private readonly \Throwable $throwable
    ) {
    }

    public function fetchRequestedFromBristolCityCouncilJson(): string
    {
        throw $this->throwable;
    }
}
