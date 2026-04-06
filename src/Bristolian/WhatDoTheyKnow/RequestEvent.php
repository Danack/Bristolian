<?php

declare(strict_types=1);

namespace Bristolian\WhatDoTheyKnow;

/**
 * One entry from the WhatDoTheyKnow JSON feed of request activity (e.g. responses, follow-ups).
 */
final readonly class RequestEvent
{
    public function __construct(
        public int $id,
        public string $event_type,
        public string $created_at,
        public ?string $described_state,
        public ?string $calculated_state,
        public ?string $last_described_at,
        public ?int $incoming_message_id,
        public ?int $outgoing_message_id,
        public ?int $comment_id,
        public string $display_status,
        public string $snippet,
        public InfoRequest $info_request,
        public PublicBody $public_body,
        public RequestEventUser $user
    ) {
    }
}
