<?php

declare(strict_types=1);

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;

/**
 * Parse TRO (Traffic Regulation Order) entries from Bristol City Council HTML page content.
 *
 * @return BccTro[]
 */
function parseTrosFromHtml(string $html): array
{
    if (empty(trim($html))) {
        return [];
    }

    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new \DOMXPath($dom);

    $tros = [];

    $h3Elements = $xpath->query('//h3');

    foreach ($h3Elements as $h3) {
        $title = trim($h3->textContent);

        if ($title === '' || strpos($title, ':') === false) {
            continue;
        }

        $nextH4 = $xpath->query('following-sibling::h4[1]', $h3)->item(0);

        if ($nextH4 !== null) {
            $referenceCode = trim($nextH4->textContent);

            $nextUlNode = $xpath->query('following::ul[.//a][1]', $nextH4)->item(0);
            $nextUl = ($nextUlNode instanceof \DOMElement) ? $nextUlNode : null;

            if ($nextUl === null) {
                $documents = [];
            }
            else {
                $documents = extractDocumentLinksFromUl($xpath, $nextUl);
            }

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

/**
 * Extract document links from a UL element (statement of reasons, notice, plan).
 *
 * @return array<string, BccTroDocument>
 */
function extractDocumentLinksFromUl(\DOMXPath $xpath, \DOMElement $ulElement): array
{
    $documents = [];

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
