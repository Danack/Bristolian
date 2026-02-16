<?php

namespace Bristolian\Service\BccTroFetcher;

use Bristolian\Model\Types\BccTro;
use Bristolian\Service\HttpFetcher\HttpFetcher;

class StandardBccTroFetcher implements BccTroFetcher
{

    private const SOURCE_URL = 'https://www.bristol.gov.uk/residents/streets-travel/make-a-comment-on-traffic-regulation-orders-tros';

    public function __construct(
        private readonly HttpFetcher $httpFetcher
    ) {
    }

    /**
     * @return BccTro[]
     */
    public function fetchTros(): array
    {
        $htmlContent = $this->fetchHtmlContent();

        return \parseTrosFromHtml($htmlContent);
    }


    private function fetchHtmlContent(): string
    {
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ];

        [$statusCode, $htmlContent, $responseHeaders] = $this->httpFetcher->fetch(
            self::SOURCE_URL,
            'GET',
            [],
            null,
            $headers
        );

        if ($statusCode !== 200) {
            throw new \RuntimeException("Failed to fetch content from " . self::SOURCE_URL . " (HTTP " . $statusCode . ")");
        }

        return $htmlContent;
    }
}
