<?php

declare(strict_types=1);

use Bristolian\Model\Types\BccTro;
use Bristolian\Model\Types\BccTroDocument;
use function Safe\preg_match;
use function Safe\parse_url;

/**
 * Parse TRO (Traffic Regulation Order) entries from Bristol City Council HTML page content.
 *
 * Relative document hrefs are resolved against $baseUrl so stored links are absolute.
 *
 * @return BccTro[]
 */
function parseTrosFromHtml(
    string $html,
    string $baseUrl = 'https://www.bristol.gov.uk'
): array {
    if (empty(trim($html))) {
        return [];
    }

    $dom = new \DOMDocument();

    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new \DOMXPath($dom);

    $tros = [];

    foreach ($xpath->query('//h3') as $h3) {
        $title = trim($h3->textContent);

        if ($title === '') {
            continue;
        }

        $referenceCode = '';

        // First look for an h4 immediately associated with this TRO.
        $nextElement = $h3->nextSibling;

        while ($nextElement !== null) {
            if ($nextElement instanceof \DOMElement
                && strtolower($nextElement->tagName) === 'h3'
            ) {
                break;
            }

            if ($nextElement instanceof \DOMElement
                && strtolower($nextElement->tagName) === 'h4'
            ) {
                $referenceCode = trim($nextElement->textContent);
                break;
            }

            $nextElement = $nextElement->nextSibling;
        }

        // If no h4 exists, extract the reference from the title.
        if ($referenceCode === '') {
            if (preg_match(
                '/\b(?:ref(?:erence)?\.?\s*)?([A-Z]{1,10}-[A-Z]{1,10}-\d{2}-\d{3})\b/i',
                $title,
                $matches
            )) {
                $referenceCode = $matches[1];
            }
        }

        $documents = [];

        // Walk forward until we hit the next h3.
        $node = $h3->nextSibling;

        while ($node !== null) {
            if ($node instanceof \DOMElement
                && strtolower($node->tagName) === 'h3'
            ) {
                break;
            }

            if ($node instanceof \DOMElement
                && strtolower($node->tagName) === 'ul'
            ) {
                $candidateDocuments = extractDocumentLinksFromUl(
                    $xpath,
                    $node,
                    $baseUrl
                );

                if (isset($candidateDocuments['statement_of_reasons'])
                    || isset($candidateDocuments['notice_of_proposal'])
                    || isset($candidateDocuments['proposed_plan'])
                ) {
                    $documents = $candidateDocuments;
                    break;
                }
            }

            $node = $node->nextSibling;
        }

        // This is probably a heading unrelated to a TRO.
        if ($documents === []) {
            continue;
        }

        $tros[] = new BccTro(
            $title,
            $referenceCode,
            $documents['statement_of_reasons'] ?? new BccTroDocument('', '', ''),
            $documents['notice_of_proposal'] ?? new BccTroDocument('', '', ''),
            $documents['proposed_plan'] ?? new BccTroDocument('', '', '')
        );
    }

    return $tros;
}

/**
 * Turn a document href from BCC HTML into an absolute URL.
 *
 * Already-absolute http(s) hrefs are left unchanged. Root-relative paths
 * (e.g. /files/documents/...) and protocol-relative hrefs are resolved
 * against the scheme/host of $baseUrl.
 */
function resolveBccDocumentHref(string $href, string $baseUrl): string
{
    $href = trim($href);
    if ($href === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $href) === 1) {
        return $href;
    }

    $baseParts = parse_url($baseUrl);
    $scheme = $baseParts['scheme'] ?? 'https';
    $host = $baseParts['host'] ?? 'www.bristol.gov.uk';
    $origin = $scheme . '://' . $host;

    if (str_starts_with($href, '//')) {
        return $scheme . ':' . $href;
    }

    if (str_starts_with($href, '/')) {
        return $origin . $href;
    }

    $basePath = $baseParts['path'] ?? '/';
    $directory = rtrim(str_replace('\\', '/', dirname($basePath)), '/');
    if ($directory === '' || $directory === '.') {
        $directory = '';
    }

    return $origin . $directory . '/' . $href;
}

/**
 * Extract document links from a UL element (statement of reasons, notice, plan).
 *
 * @return array<string, BccTroDocument>
 */
function extractDocumentLinksFromUl(
    \DOMXPath $xpath,
    \DOMElement $ulElement,
    string $baseUrl = 'https://www.bristol.gov.uk'
): array {
    $documents = [];

    $linkElements = $xpath->query('.//a', $ulElement);

    foreach ($linkElements as $linkNode) {
        if (!($linkNode instanceof \DOMElement)) {
            // @codeCoverageIgnoreStart
            continue;
            // @codeCoverageIgnoreEnd
        }
        $link = $linkNode;
        $href = resolveBccDocumentHref($link->getAttribute('href'), $baseUrl);
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


/**
 * Compare two TRO data arrays for equality, ignoring JSON object key order
 * (MySQL JSON columns may reorder keys on store/retrieve).
 */
function bccTroDataEquals(array $left, array $right): bool
{
    return json_encode_safe(sortArrayKeysRecursive($left))
        === json_encode_safe(sortArrayKeysRecursive($right));
}

/**
 * @param mixed $value
 * @return mixed
 */
function sortArrayKeysRecursive(mixed $value): mixed
{
    if (is_array($value) !== true) {
        return $value;
    }

    $isList = array_is_list($value);
    $sorted = [];
    foreach ($value as $key => $item) {
        $sorted[$key] = sortArrayKeysRecursive($item);
    }

    if ($isList !== true) {
        ksort($sorted);
    }

    return $sorted;
}


/**
 * @param BccTro[] $tros
 * @return string
 */
function output_tro_list_to_output($tros): string
{
    if (empty($tros)) {
        return "No TROs found.\n";
    }

    $output = "Found " . count($tros) . " TRO(s):\n\n";

    foreach ($tros as $tro) {
        $output .= "Title: " . $tro->title . "\n";
        $output .= "Reference: " . $tro->reference_code . "\n";

        if (!empty($tro->statement_of_reasons->title)) {
            $output .= "Statement of Reasons: " . $tro->statement_of_reasons->title . "\n";
            $output .= "  Link: " . $tro->statement_of_reasons->href . "\n";
        }

        if (!empty($tro->notice_of_proposal->title)) {
            $output .= "Notice of Proposal: " . $tro->notice_of_proposal->title . "\n";
            $output .= "  Link: " . $tro->notice_of_proposal->href . "\n";
        }

        if (!empty($tro->proposed_plan->title)) {
            $output .= "Proposed Plan: " . $tro->proposed_plan->title . "\n";
            $output .= "  Link: " . $tro->proposed_plan->href . "\n";
        }

        $output .= "---\n";
    }

    return $output;
}


/**
 * @param BccTro[] $tros
 * @return string
 */
function renderBccTrosAsMarkdown(array $tros): string
{
    $number_of_tros = count($tros);
    if ($number_of_tros === 0) {
        return "There are no TROs.";
    }

    $output = sprintf("There are %s TROs.", $number_of_tros);

    foreach ($tros as $tro) {
        $output .= renderBccTroAsMarkdown($tro);
    }

    return $output;
}


/**
 * Generates appropriate markdown for a TRO to be used in a chat room.
 *
 * @param BccTro $tro
 * @return string
 */
function renderBccTroAsMarkdown(BccTro $tro): string
{
    $lines = [];

    $lines[] = sprintf('## %s', $tro->title);
    $lines[] = '';

    if ($tro->reference_code !== '') {
        $lines[] = sprintf('**Reference:** %s', $tro->reference_code);
        $lines[] = '';
    }

    $documents = [
        'Statement of Reasons' => $tro->statement_of_reasons,
        'Notice of Proposal' => $tro->notice_of_proposal,
        'Proposed Plan' => $tro->proposed_plan,
    ];

    foreach ($documents as $label => $document) {
        $lines[] = sprintf(
            '- [%s](%s)',
            $label,
            $document->href
        );
    }

    return implode("\n", $lines);
}
