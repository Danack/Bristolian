<?php

declare(strict_types=1);

use Bristolian\WhatDoTheyKnow\InfoRequest;
use Bristolian\WhatDoTheyKnow\PublicBody;
use Bristolian\WhatDoTheyKnow\PublicBodyRequestCounts;
use Bristolian\WhatDoTheyKnow\PublicBodyTag;
use Bristolian\WhatDoTheyKnow\RequestEvent;
use Bristolian\WhatDoTheyKnow\RequestEventUser;

/**
 * Parse WhatDoTheyKnow JSON (array of request events, e.g. "requested from" feed).
 *
 * @return list<RequestEvent>
 */
function parseWhatDoTheyKnowRequestEventsJson(string $json): array
{
    $decoded = json_decode_safe($json);

    if (array_is_list($decoded) === false) {
        throw new \InvalidArgumentException('Expected JSON array of request events at top level.');
    }

    $events = [];
    foreach ($decoded as $index => $item) {
        if (is_array($item) === false) {
            throw new \InvalidArgumentException(sprintf('Request event at index %s must be an object.', (string)$index));
        }
        /** @var array<string, mixed> $item */
        $events[] = whatdotheyknowRequestEventFromArray($item);
    }

    return $events;
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowRequestEventFromArray(array $data): RequestEvent
{
    return new RequestEvent(
        id: whatdotheyknowRequireInt($data, 'id'),
        event_type: whatdotheyknowRequireString($data, 'event_type'),
        created_at: whatdotheyknowRequireString($data, 'created_at'),
        described_state: whatdotheyknowOptionalString($data, 'described_state'),
        calculated_state: whatdotheyknowOptionalString($data, 'calculated_state'),
        last_described_at: whatdotheyknowOptionalString($data, 'last_described_at'),
        incoming_message_id: whatdotheyknowOptionalInt($data, 'incoming_message_id'),
        outgoing_message_id: whatdotheyknowOptionalInt($data, 'outgoing_message_id'),
        comment_id: whatdotheyknowOptionalInt($data, 'comment_id'),
        display_status: whatdotheyknowRequireString($data, 'display_status'),
        snippet: whatdotheyknowRequireString($data, 'snippet'),
        info_request: whatdotheyknowInfoRequestFromArray(whatdotheyknowRequireArray($data, 'info_request')),
        public_body: whatdotheyknowPublicBodyFromArray(whatdotheyknowRequireArray($data, 'public_body')),
        user: whatdotheyknowRequestEventUserFromArray(whatdotheyknowRequireArray($data, 'user'))
    );
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowInfoRequestFromArray(array $data): InfoRequest
{
    $tagsRaw = whatdotheyknowRequireArray($data, 'tags');
    $tags = [];
    foreach ($tagsRaw as $tagIndex => $tagValue) {
        if (is_string($tagValue) === false) {
            throw new \InvalidArgumentException(sprintf('info_request.tags[%s] must be a string.', (string)$tagIndex));
        }
        $tags[] = $tagValue;
    }

    return new InfoRequest(
        id: whatdotheyknowRequireInt($data, 'id'),
        url_title: whatdotheyknowRequireString($data, 'url_title'),
        title: whatdotheyknowRequireString($data, 'title'),
        created_at: whatdotheyknowRequireString($data, 'created_at'),
        updated_at: whatdotheyknowRequireString($data, 'updated_at'),
        described_state: whatdotheyknowRequireString($data, 'described_state'),
        display_status: whatdotheyknowRequireString($data, 'display_status'),
        awaiting_description: whatdotheyknowRequireBool($data, 'awaiting_description'),
        prominence: whatdotheyknowRequireString($data, 'prominence'),
        law_used: whatdotheyknowRequireString($data, 'law_used'),
        tags: $tags
    );
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowPublicBodyFromArray(array $data): PublicBody
{
    $tagsRaw = whatdotheyknowRequireArray($data, 'tags');
    $tags = [];
    foreach ($tagsRaw as $tagIndex => $tagRow) {
        if (is_array($tagRow) === false || array_is_list($tagRow) === false || count($tagRow) !== 2) {
            throw new \InvalidArgumentException(sprintf('public_body.tags[%s] must be a two-element array.', (string)$tagIndex));
        }
        $key = $tagRow[0];
        $value = $tagRow[1];
        if (is_string($key) === false) {
            throw new \InvalidArgumentException(sprintf('public_body.tags[%s][0] must be a string.', (string)$tagIndex));
        }
        if ($value !== null && is_string($value) === false) {
            throw new \InvalidArgumentException(sprintf('public_body.tags[%s][1] must be a string or null.', (string)$tagIndex));
        }
        $tags[] = new PublicBodyTag($key, $value);
    }

    $info = whatdotheyknowRequireArray($data, 'info');

    return new PublicBody(
        id: whatdotheyknowRequireInt($data, 'id'),
        url_name: whatdotheyknowRequireString($data, 'url_name'),
        name: whatdotheyknowRequireString($data, 'name'),
        short_name: whatdotheyknowOptionalString($data, 'short_name'),
        created_at: whatdotheyknowRequireString($data, 'created_at'),
        updated_at: whatdotheyknowRequireString($data, 'updated_at'),
        home_page: whatdotheyknowRequireString($data, 'home_page'),
        notes: whatdotheyknowRequireString($data, 'notes'),
        publication_scheme: whatdotheyknowRequireString($data, 'publication_scheme'),
        disclosure_log: whatdotheyknowRequireString($data, 'disclosure_log'),
        tags: $tags,
        request_counts: whatdotheyknowPublicBodyRequestCountsFromArray($info)
    );
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowPublicBodyRequestCountsFromArray(array $data): PublicBodyRequestCounts
{
    return new PublicBodyRequestCounts(
        requests_count: whatdotheyknowRequireInt($data, 'requests_count'),
        requests_successful_count: whatdotheyknowRequireInt($data, 'requests_successful_count'),
        requests_not_held_count: whatdotheyknowRequireInt($data, 'requests_not_held_count'),
        requests_overdue_count: whatdotheyknowRequireInt($data, 'requests_overdue_count'),
        requests_visible_classified_count: whatdotheyknowRequireInt($data, 'requests_visible_classified_count')
    );
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowRequestEventUserFromArray(array $data): RequestEventUser
{
    return new RequestEventUser(
        id: whatdotheyknowRequireInt($data, 'id'),
        url_name: whatdotheyknowRequireString($data, 'url_name'),
        name: whatdotheyknowRequireString($data, 'name'),
        ban_text: whatdotheyknowRequireString($data, 'ban_text'),
        about_me: whatdotheyknowRequireString($data, 'about_me')
    );
}

/**
 * @param array<string, mixed> $data
 * @return array<string, mixed>
 */
function whatdotheyknowRequireArray(array $data, string $key): array
{
    if (array_key_exists($key, $data) === false) {
        throw new \InvalidArgumentException(sprintf('Missing key "%s".', $key));
    }
    $value = $data[$key];
    if (is_array($value) === false) {
        throw new \InvalidArgumentException(sprintf('Key "%s" must be an object.', $key));
    }

    return $value;
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowRequireString(array $data, string $key): string
{
    if (array_key_exists($key, $data) === false) {
        throw new \InvalidArgumentException(sprintf('Missing key "%s".', $key));
    }
    $value = $data[$key];
    if (is_string($value) === false) {
        throw new \InvalidArgumentException(sprintf('Key "%s" must be a string.', $key));
    }

    return $value;
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowOptionalString(array $data, string $key): ?string
{
    if (array_key_exists($key, $data) === false) {
        return null;
    }
    $value = $data[$key];
    if ($value === null) {
        return null;
    }
    if (is_string($value) === false) {
        throw new \InvalidArgumentException(sprintf('Key "%s" must be a string or null.', $key));
    }

    return $value;
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowRequireInt(array $data, string $key): int
{
    if (array_key_exists($key, $data) === false) {
        throw new \InvalidArgumentException(sprintf('Missing key "%s".', $key));
    }
    $value = $data[$key];
    if (is_int($value) === false) {
        throw new \InvalidArgumentException(sprintf('Key "%s" must be an integer.', $key));
    }

    return $value;
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowOptionalInt(array $data, string $key): ?int
{
    if (array_key_exists($key, $data) === false) {
        return null;
    }
    $value = $data[$key];
    if ($value === null) {
        return null;
    }
    if (is_int($value) === false) {
        throw new \InvalidArgumentException(sprintf('Key "%s" must be an integer or null.', $key));
    }

    return $value;
}

/**
 * @param array<string, mixed> $data
 */
function whatdotheyknowRequireBool(array $data, string $key): bool
{
    if (array_key_exists($key, $data) === false) {
        throw new \InvalidArgumentException(sprintf('Missing key "%s".', $key));
    }
    $value = $data[$key];
    if (is_bool($value) === false) {
        throw new \InvalidArgumentException(sprintf('Key "%s" must be a boolean.', $key));
    }

    return $value;
}

/**
 * Parse a single request-event object from decoded JSON (one element of the feed array).
 *
 * @param array<string, mixed> $data
 */
function parseWhatDoTheyKnowRequestEventFromArray(array $data): RequestEvent
{
    return whatdotheyknowRequestEventFromArray($data);
}

/**
 * Normalise WhatDoTheyKnow's top-level event `created_at` to UTC for MySQL storage.
 */
function whatDoTheyKnowWdtEventOccurredAtUtc(string $createdAtFromApi): \DateTimeImmutable
{
    return (new \DateTimeImmutable($createdAtFromApi))->setTimezone(new \DateTimeZone('UTC'));
}

/**
 * Public request page URL for an `info_request.url_title` slug.
 */
function whatDoTheyKnowRequestUrlFromUrlTitle(string $urlTitle): string
{
    return 'https://www.whatdotheyknow.com/request/' . $urlTitle;
}



function buildNewEventRoomMessageText(RequestEvent $event, string $requestUrl): string
{
    $lines = [
        'WhatDoTheyKnow update (' . $event->event_type . ', ' . $event->display_status . ')',
        'Request: [' . $event->info_request->title . "]($requestUrl)",
//            $requestUrl,
//            'Requester: ' . $event->user->name,
    ];

    return implode("\n", $lines);
}