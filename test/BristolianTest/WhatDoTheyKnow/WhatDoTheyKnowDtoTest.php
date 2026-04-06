<?php

declare(strict_types=1);

namespace BristolianTest\WhatDoTheyKnow;

use Bristolian\WhatDoTheyKnow\InfoRequest;
use Bristolian\WhatDoTheyKnow\PublicBody;
use Bristolian\WhatDoTheyKnow\PublicBodyRequestCounts;
use Bristolian\WhatDoTheyKnow\PublicBodyTag;
use Bristolian\WhatDoTheyKnow\RequestEvent;
use Bristolian\WhatDoTheyKnow\RequestEventUser;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
final class WhatDoTheyKnowDtoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\WhatDoTheyKnow\PublicBodyTag::__construct
     */
    public function test_PublicBodyTag_holds_key_and_value(): void
    {
        $tag = new PublicBodyTag('k', 'v');
        self::assertSame('k', $tag->key);
        self::assertSame('v', $tag->value);
    }

    /**
     * @covers \Bristolian\WhatDoTheyKnow\PublicBodyRequestCounts::__construct
     */
    public function test_PublicBodyRequestCounts_holds_counts(): void
    {
        $counts = new PublicBodyRequestCounts(1, 2, 3, 4, 5);
        self::assertSame(1, $counts->requests_count);
        self::assertSame(5, $counts->requests_visible_classified_count);
    }

    /**
     * @covers \Bristolian\WhatDoTheyKnow\InfoRequest::__construct
     */
    public function test_InfoRequest_holds_fields(): void
    {
        $info = new InfoRequest(
            id: 1,
            url_title: 'slug',
            title: 'Title',
            created_at: '2026-01-01T00:00:00+00:00',
            updated_at: '2026-01-01T00:00:00+00:00',
            described_state: 'successful',
            display_status: 'OK',
            awaiting_description: false,
            prominence: 'normal',
            law_used: 'foi',
            tags: []
        );
        self::assertSame('slug', $info->url_title);
    }

    /**
     * @covers \Bristolian\WhatDoTheyKnow\RequestEventUser::__construct
     */
    public function test_RequestEventUser_holds_fields(): void
    {
        $user = new RequestEventUser(1, 'url', 'Name', '', '');
        self::assertSame('Name', $user->name);
    }

    /**
     * @covers \Bristolian\WhatDoTheyKnow\PublicBody::__construct
     */
    public function test_PublicBody_holds_fields(): void
    {
        $counts = new PublicBodyRequestCounts(0, 0, 0, 0, 0);
        $body = new PublicBody(
            id: 90,
            url_name: 'bristol',
            name: 'Bristol',
            short_name: null,
            created_at: '2008-01-01T00:00:00+00:00',
            updated_at: '2026-01-01T00:00:00+00:00',
            home_page: 'https://example.com',
            notes: '',
            publication_scheme: '',
            disclosure_log: '',
            tags: [],
            request_counts: $counts
        );
        self::assertSame('Bristol', $body->name);
    }

    /**
     * @covers \Bristolian\WhatDoTheyKnow\RequestEvent::__construct
     */
    public function test_RequestEvent_holds_nested_objects(): void
    {
        $info = new InfoRequest(
            id: 1,
            url_title: 's',
            title: 'T',
            created_at: '2026-01-01T00:00:00+00:00',
            updated_at: '2026-01-01T00:00:00+00:00',
            described_state: 'successful',
            display_status: 'OK',
            awaiting_description: false,
            prominence: 'normal',
            law_used: 'foi',
            tags: []
        );
        $counts = new PublicBodyRequestCounts(0, 0, 0, 0, 0);
        $body = new PublicBody(
            id: 90,
            url_name: 'b',
            name: 'B',
            short_name: null,
            created_at: '2008-01-01T00:00:00+00:00',
            updated_at: '2026-01-01T00:00:00+00:00',
            home_page: 'https://example.com',
            notes: '',
            publication_scheme: '',
            disclosure_log: '',
            tags: [],
            request_counts: $counts
        );
        $user = new RequestEventUser(1, 'u', 'U', '', '');
        $event = new RequestEvent(
            id: 10,
            event_type: 'response',
            created_at: '2026-01-01T12:00:00+00:00',
            described_state: null,
            calculated_state: null,
            last_described_at: null,
            incoming_message_id: null,
            outgoing_message_id: null,
            comment_id: null,
            display_status: 'OK',
            snippet: 'x',
            info_request: $info,
            public_body: $body,
            user: $user
        );
        self::assertSame(10, $event->id);
        self::assertSame('response', $event->event_type);
    }
}
