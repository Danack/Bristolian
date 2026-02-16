<?php

namespace Bristolian\Service\BccTroFetcher;

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
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

        return $this->parseTrosFromHtml($htmlContent);
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


    /**
     * @return BccTro[]
     */
    public function parseTrosFromHtml(string $html): array
    {
        // Handle empty HTML
        if (empty(trim($html))) {
            return [];
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $tros = [];

        // Find all h3 elements that contain TRO titles
        $h3Elements = $xpath->query('//h3');

        foreach ($h3Elements as $h3) {
            $title = trim($h3->textContent);

            // Skip if not a TRO title (based on example format)
            if ($title === '' || strpos($title, ':') === false) {
                continue;
            }

            // Find the next h4 element which should contain the reference code
            $nextH4 = $xpath->query('following-sibling::h4[1]', $h3)->item(0);

            if ($nextH4 !== null) {
                $referenceCode = trim($nextH4->textContent);

                // Find the ul element that contains the document links
                // Look for ul elements that come after the h4 and contain links
                $nextUlNode = $xpath->query('following::ul[.//a][1]', $nextH4)->item(0);
                $nextUl = ($nextUlNode instanceof \DOMElement) ? $nextUlNode : null;

                $documents = extractDocumentLinksFromUl($xpath, $nextUl);

                $tros[] = new BccTro(
                    $title,
                    $referenceCode,
                    $documents['statement_of_reasons'] ?? new BccTroDocument('', '', ''),
                    $documents['notice_of_proposal'] ?? new BccTroDocument('', '', ''),
                    $documents['proposed_plan'] ?? new BccTroDocument('', '', '')
                );
            }
        }

        return $tros;
    }
}

/**
 * @return array<string, BccTroDocument>
 */
function extractDocumentLinksFromUl(\DOMXPath $xpath, ?\DOMElement $ulElement): array
{
    $documents = [];

    if ($ulElement === null) {
        return $documents;
    }

    $linkElements = $xpath->query('.//a', $ulElement);

    foreach ($linkElements as $linkNode) {
        if (!($linkNode instanceof \DOMElement)) {
            continue;
        }
        $link = $linkNode;
        $href = $link->getAttribute('href');
        $linkText = trim($link->textContent);

        $id = $link->getAttribute('data-id');
        if (empty($id) && preg_match('/\/(\d+)-/', $href, $matches)) {
            $id = $matches[1];
        }

        $title = $link->getAttribute('data-title');
        if (empty($title)) {
            $title = $linkText;
        }

        $linkTextLower = strtolower($linkText);

        if (strpos($linkTextLower, 'statement of reasons') !== false) {
            $documents['statement_of_reasons'] = new BccTroDocument($title, $href, $id);
        }
        if (strpos($linkTextLower, 'notice') !== false) {
            $documents['notice_of_proposal'] = new BccTroDocument($title, $href, $id);
        }
        if (strpos($linkTextLower, 'plan') !== false) {
            $documents['proposed_plan'] = new BccTroDocument($title, $href, $id);
        }
    }

    return $documents;
}
