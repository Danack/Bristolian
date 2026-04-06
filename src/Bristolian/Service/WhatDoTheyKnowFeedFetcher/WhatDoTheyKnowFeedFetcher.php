<?php

declare(strict_types=1);

namespace Bristolian\Service\WhatDoTheyKnowFeedFetcher;

interface WhatDoTheyKnowFeedFetcher
{
    /**
     * Raw JSON body from the "requested from: bristol_city_council" feed.
     */
    public function fetchRequestedFromBristolCityCouncilJson(): string;
}
