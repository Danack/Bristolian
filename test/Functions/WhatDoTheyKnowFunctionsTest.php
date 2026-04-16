<?php

declare(strict_types=1);

namespace Functions;

use Bristolian\WhatDoTheyKnow\RequestEvent;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
final class WhatDoTheyKnowFunctionsTest extends BaseTestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function minimalValidRequestEventArray(): array
    {
        return [
            'id' => 1,
            'event_type' => 'response',
            'created_at' => '2026-01-01T12:00:00.000+00:00',
            'display_status' => 'Successful',
            'snippet' => 'Hello',
            'info_request' => [
                'id' => 2,
                'url_title' => 'test_request_slug',
                'title' => 'Test title',
                'created_at' => '2026-01-01T10:00:00.000+00:00',
                'updated_at' => '2026-01-01T11:00:00.000+00:00',
                'described_state' => 'successful',
                'display_status' => 'Successful',
                'awaiting_description' => false,
                'prominence' => 'normal',
                'law_used' => 'foi',
                'tags' => [],
            ],
            'public_body' => [
                'id' => 90,
                'url_name' => 'bristol_city_council',
                'name' => 'Bristol City Council',
                'short_name' => null,
                'created_at' => '2008-03-03T18:42:02.833+00:00',
                'updated_at' => '2026-01-02T04:10:07.118+01:00',
                'home_page' => 'https://www.bristol.gov.uk/',
                'notes' => 'Notes',
                'publication_scheme' => 'https://example.com/scheme',
                'disclosure_log' => '',
                'tags' => [],
                'info' => [
                    'requests_count' => 1,
                    'requests_successful_count' => 0,
                    'requests_not_held_count' => 0,
                    'requests_overdue_count' => 0,
                    'requests_visible_classified_count' => 1,
                ],
            ],
            'user' => [
                'id' => 3,
                'url_name' => 'test_user',
                'name' => 'Test User',
                'ban_text' => '',
                'about_me' => '',
            ],
        ];
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventsJson
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     * @covers \whatdotheyknowPublicBodyRequestCountsFromArray
     * @covers \whatdotheyknowRequestEventUserFromArray
     * @covers \whatdotheyknowRequireArray
     * @covers \whatdotheyknowRequireString
     * @covers \whatdotheyknowOptionalString
     * @covers \whatdotheyknowRequireInt
     * @covers \whatdotheyknowOptionalInt
     * @covers \whatdotheyknowRequireBool
     */
    public function test_parseWhatDoTheyKnowRequestEventsJson_parses_fixture(): void
    {
        $path = dirname(__DIR__) . '/fixtures/whatdotheyknow/requested_from_bristol_city_council.json';
        $json = file_get_contents($path);
        self::assertNotFalse($json);

        $events = parseWhatDoTheyKnowRequestEventsJson($json);

        self::assertCount(25, $events);
        self::assertContainsOnlyInstancesOf(RequestEvent::class, $events);

        $first = $events[0];
        self::assertSame(19881582, $first->id);
        self::assertSame('response', $first->event_type);
        self::assertSame('Bristol City Council', $first->public_body->name);
        self::assertSame(1416480, $first->info_request->id);
        self::assertSame('Clarisa', $first->user->name);
        self::assertSame('addressbase_custodian', $first->public_body->tags[0]->key);
        self::assertSame('116', $first->public_body->tags[0]->value);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventsJson
     */
    public function test_parseWhatDoTheyKnowRequestEventsJson_rejects_non_list_top_level(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected JSON array of request events at top level.');

        parseWhatDoTheyKnowRequestEventsJson('{"id":1}');
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventsJson
     */
    public function test_parseWhatDoTheyKnowRequestEventsJson_rejects_non_object_element(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Request event at index 0 must be an object.');

        parseWhatDoTheyKnowRequestEventsJson('[1]');
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_matches_first_fixture_element(): void
    {
        $path = dirname(__DIR__) . '/fixtures/whatdotheyknow/requested_from_bristol_city_council.json';
        $json = file_get_contents($path);
        self::assertNotFalse($json);
        $list = json_decode($json, true);
        self::assertIsArray($list);
        self::assertIsArray($list[0]);

        $event = parseWhatDoTheyKnowRequestEventFromArray($list[0]);

        self::assertSame(19881582, $event->id);
        self::assertSame(1416480, $event->info_request->id);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowOptionalString
     * @covers \whatdotheyknowOptionalInt
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     * @covers \whatdotheyknowPublicBodyRequestCountsFromArray
     * @covers \whatdotheyknowRequestEventUserFromArray
     * @covers \whatdotheyknowRequireArray
     * @covers \whatdotheyknowRequireString
     * @covers \whatdotheyknowRequireInt
     * @covers \whatdotheyknowRequireBool
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_accepts_omitted_optional_event_keys(): void
    {
        $data = self::minimalValidRequestEventArray();
        unset($data['described_state'], $data['calculated_state'], $data['last_described_at']);
        unset($data['incoming_message_id'], $data['outgoing_message_id'], $data['comment_id']);
        unset($data['public_body']['short_name']);

        $event = parseWhatDoTheyKnowRequestEventFromArray($data);

        self::assertNull($event->described_state);
        self::assertNull($event->incoming_message_id);
        self::assertNull($event->public_body->short_name);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowRequireInt
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_top_level_id_missing(): void
    {
        $data = self::minimalValidRequestEventArray();
        unset($data['id']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key "id".');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowRequireString
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_event_type_not_string(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['event_type'] = 404;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "event_type" must be a string.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowRequireArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_info_request_missing(): void
    {
        $data = self::minimalValidRequestEventArray();
        unset($data['info_request']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key "info_request".');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowRequireArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_info_request_not_object(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['info_request'] = 'not-an-object';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "info_request" must be an object.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_info_request_tag_not_string(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['info_request']['tags'] = ['ok', 99];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('info_request.tags[1] must be a string.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowRequireString
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_info_request_title_missing(): void
    {
        $data = self::minimalValidRequestEventArray();
        unset($data['info_request']['title']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key "title".');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowRequireInt
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_info_request_id_not_integer(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['info_request']['id'] = 'not-int';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "id" must be an integer.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowRequireBool
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_awaiting_description_missing(): void
    {
        $data = self::minimalValidRequestEventArray();
        unset($data['info_request']['awaiting_description']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key "awaiting_description".');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_public_body_tag_not_pair(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['public_body']['tags'] = [['only_one']];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('public_body.tags[0] must be a two-element array.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_public_body_tag_key_not_string(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['public_body']['tags'] = [[99, 'value']];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('public_body.tags[0][0] must be a string.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_public_body_tag_value_not_string_or_null(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['public_body']['tags'] = [['some_key', 42]];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('public_body.tags[0][1] must be a string or null.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     * @covers \whatdotheyknowOptionalString
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_short_name_not_string_or_null(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['public_body']['short_name'] = 123;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "short_name" must be a string or null.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowOptionalInt
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_optional_int_not_int_or_null(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['incoming_message_id'] = '3367905';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "incoming_message_id" must be an integer or null.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowRequireBool
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_awaiting_description_not_bool(): void
    {
        $data = self::minimalValidRequestEventArray();
        $data['info_request']['awaiting_description'] = 1;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key "awaiting_description" must be a boolean.');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     * @covers \whatdotheyknowPublicBodyRequestCountsFromArray
     * @covers \whatdotheyknowRequireInt
     */
    public function test_parseWhatDoTheyKnowRequestEventFromArray_throws_when_request_counts_missing_field(): void
    {
        $data = self::minimalValidRequestEventArray();
        unset($data['public_body']['info']['requests_overdue_count']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key "requests_overdue_count".');

        parseWhatDoTheyKnowRequestEventFromArray($data);
    }

    /**
     * @covers \whatDoTheyKnowWdtEventOccurredAtUtc
     */
    public function test_whatDoTheyKnowWdtEventOccurredAtUtc_normalises_to_utc(): void
    {
        $utc = whatDoTheyKnowWdtEventOccurredAtUtc('2026-04-02T15:00:44.392+01:00');
        self::assertSame('2026-04-02 14:00:44', $utc->format('Y-m-d H:i:s'));
    }

    /**
     * @covers \whatDoTheyKnowRequestUrlFromUrlTitle
     */
    public function test_whatDoTheyKnowRequestUrlFromUrlTitle_builds_url(): void
    {
        self::assertSame(
            'https://www.whatdotheyknow.com/request/provide_the_reports_for_the_hous',
            whatDoTheyKnowRequestUrlFromUrlTitle('provide_the_reports_for_the_hous')
        );
    }

    /**
     * @covers \buildNewEventRoomMessageText
     * @covers \parseWhatDoTheyKnowRequestEventFromArray
     * @covers \whatdotheyknowRequestEventFromArray
     * @covers \whatdotheyknowInfoRequestFromArray
     * @covers \whatdotheyknowPublicBodyFromArray
     * @covers \whatdotheyknowPublicBodyRequestCountsFromArray
     * @covers \whatdotheyknowRequestEventUserFromArray
     * @covers \whatdotheyknowRequireArray
     * @covers \whatdotheyknowRequireString
     * @covers \whatdotheyknowOptionalString
     * @covers \whatdotheyknowRequireInt
     * @covers \whatdotheyknowOptionalInt
     * @covers \whatdotheyknowRequireBool
     */
    public function test_buildNewEventRoomMessageText_formats_summary_lines(): void
    {
        $event = parseWhatDoTheyKnowRequestEventFromArray(self::minimalValidRequestEventArray());
        $requestUrl = 'https://www.whatdotheyknow.com/request/test_request_slug';

        $messageText = buildNewEventRoomMessageText($event, $requestUrl);

        self::assertSame(
            "WhatDoTheyKnow update (response, Successful)\nRequest: [Test title](https://www.whatdotheyknow.com/request/test_request_slug)",
            $messageText
        );
    }
}
