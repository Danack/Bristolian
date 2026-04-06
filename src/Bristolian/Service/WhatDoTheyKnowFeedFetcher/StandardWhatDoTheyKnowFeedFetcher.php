<?php

declare(strict_types=1);

namespace Bristolian\Service\WhatDoTheyKnowFeedFetcher;

use Bristolian\Service\HttpFetcher\HttpFetcher;

class StandardWhatDoTheyKnowFeedFetcher implements WhatDoTheyKnowFeedFetcher
{
    public const REQUESTED_FROM_BRISTOL_CITY_COUNCIL_FEED_URL =
        'https://www.whatdotheyknow.com/feed/search/requested_from:bristol_city_council.json';

    public function __construct(
        private readonly HttpFetcher $httpFetcher
    ) {
    }

    public function fetchRequestedFromBristolCityCouncilJson(): string
    {
        $headers = [
            'Accept: application/json',
            'Accept-Language: en-GB,en;q=0.9',
        ];

        [$statusCode, $body] = $this->httpFetcher->fetch(
            self::REQUESTED_FROM_BRISTOL_CITY_COUNCIL_FEED_URL,
            'GET',
            [],
            null,
            $headers
        );

        if ($statusCode !== 200) {
            throw new \RuntimeException(
                'Failed to fetch WhatDoTheyKnow feed (HTTP ' . $statusCode . ').'
            );
        }

        return $body;
    }
}
